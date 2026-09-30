# مراحل وأوجهات نظام الطلبية (Order Lifecycle)

## ملخص

يحتوي النظام على مسارين رئيسيين لإنشاء الطلبية:

1. **المسار A: Superintendent creates order (Admin Dashboard)** — يمر بـ 14حالة (State) عبر آلة آلية للحالة
2. **المسار B: Customer orders from the application** — ينشئ فاتورة مباشرة (Order + Quotation non-existent)

---

## مسار A: Superintendent creates order (Admin Dashboard)

### إنشاء الطلبية

| Step | Method | Description |
|---|---|---|
| 1 | `OrderController::store` | Admin fills the form (customer, warehouse, currency, items) |
| 2 | `OrderService::createFromCustomer` | Creates Order with status `new` |
| 3 | Stock availability check | `InventoryService::checkAvailability` |
| 4 | Pricing calculation | `PricingService::calculatePrice` |
| 5 | `OrderStatusHistory::create` | Audit log entry |

---

### States and Transitions

```
new (جديد)
  │  Admin reviews
  ▼
pending_review (قيد المراجعة)
  │  Admin edits items/prices
  ▼
admin_edited (تم التعديل)
  │  Admin sends price quote
  ▼
quotation_sent (تم إرسال العرض)
  │  InventoryService::reserve per item
  │
  ├─ Customer APPROVES ──► customer_approved (معتمد من العميل)
  │                              │
  │                              ▼ (auto)
  │                       invoice_generated (تم إنشاء الفاتورة)
  │                              │
  │                              ▼
  │                       pending_approval (قيد الموافقة)
  │                              │
  │                 ┌────────────┼────────────┐
  │                 ▼                         ▼
  │          approved (معتمد)          rejected (مرفوض)
  │                 │                  (terminal)
  │                 ▼
  │          in_delivery (قيد التوصيل)
  │          DeliveryService::assignDriver
  │                 │
  │       ┌─────────┼─────────┐
  │       ▼         ▼         ▼
  │  delivered    returned   cancelled
  │  (تم التوصيل) (مرتجع)   (ملغي)
  │  (terminal)   (terminal)  (terminal)
  │
  ├─ Customer REJECTS ──► customer_rejected (مرفض من العميل)
  │                            │
  │                            ▼ (auto)
  │                     cancelled (terminal)
  │
  └─ Customer requests changes ──► customer_requested_changes (طلب تغييرات)
                                       │
                                       ▼ (auto, inventory released)
                                admin_edited (loop back)
```

---

### Side Effects by Transition

| Transition | Side Effect |
|---|---|
| `admin_edited → quotation_sent` | `InventoryService::reserve` for each item |
| `quotation_sent → customer_rejected` | Inventory released → auto `cancelled` |
| `quotation_sent → customer_requested_changes` | Inventory released → back to `admin_edited` |
| `customer_approved → invoice_generated` | Invoice draft created (status `pending_approval`) |
| `pending_approval → approved` | `InvoiceLifecycleService::approve`: commit stock (FEFO), create delivery, set payment deadline |
| `approved → in_delivery` | `DeliveryService::assignDriver` |
| Any → `cancelled` | Inventory released if was reserved |

---

## مسار B: Customer orders from the application

### إنشاء الطلبية

| Step | Method | Description |
|---|---|---|
| 1 | `CustomerOrderController::checkout` | Customer adds items to cart → checkout |
| 2 | `CustomerAppController::checkout` | Alternative entry point |
| 3 | `Invoice::create` | Creates Invoice directly with status `pending` |
| 4 | `InvoiceItem::create` | Creates invoice items |
| 5 | No Order/Quotation created | Customer app path bypasses the Order lifecycle |

### Invoice Lifecycle (Admin processes in dashboard)

```
pending_approval (قيد الموافقة)
  │
  ├─ APPROVED ──► approved (معتمد)
  │                   │
  │                   ├─► paid (مدفوع)
  │                   ├─► partially_paid (مدفوع جزئيا)
  │                   ├─► expired (منتهي الصلاحية)
  │                   └─► cancelled (ملغي)
  │
  └─ REJECTED ──► rejected (مرفوض)
                       │
                       ▼
                cancelled (terminal)
```

---

## Terminal States (no further transitions)

| Status | Arabic | Description |
|---|---|---|
| `delivered` | تم التوصيل | Order completed successfully |
| `cancelled` | ملغي | Order cancelled |
| `returned` | مرتجع | Order returned |
| `rejected` | مرفوض | Invoice rejected |
| `expired` | منتهي الصلاحية | Payment deadline passed |

---

## Customer App → Admin Dashboard Flow

The customer application and the admin dashboard operate on the **same**
`orders` table, so every order placed from the app appears immediately in the
admin panel with the same steps and controls.

### 1. Checkout (`customer.checkout` → POST)

`CustomerOrderController::checkout()` validates the cart and delegates to
`OrderService::create()`, which creates the order at status **`new`** with
`created_by = null` (it came from the app, not the admin panel). Stock is
reserved at this point via `InventoryService`.

### 2. Steps shown to the customer

`OrderStepPresenter` renders seven customer-facing steps:

| Key | Arabic | Reached at |
|---|---|---|
| `submitted` | تم إرسال الطلب | `new` |
| `review` | قيد المراجعة | `pending_review` |
| `quotation` | عرض السعر | `quotation_sent` |
| `customer_response` | رد العميل | `customer_approved` / `rejected` / `changes_requested` |
| `invoice` | إصدار الفاتورة | `invoice_generated` |
| `delivery` | التوصيل | `in_delivery` |
| `completed` | تم التوصيل | `delivered` |

The same presenter is used by the admin view
(`dashboard/orders/partials/order-steps.blade.php`), so both sides always agree
on progress, current step, and history.

### 3. Quotation response (`customer.order.quotation.respond` → POST)

Only available while the order is at `quotation_sent`
(`OrderStepPresenter::canCustomerRespond()`):

| Response | Effect |
|---|---|
| `approved` | → `customer_approved` → auto-cascades to `invoice_generated` → `pending_approval` |
| `rejected` | order is cancelled and stock is released |
| `changes_requested` | → `admin_edited` for a revised quote |

The endpoint returns `422` for an invalid/duplicate response and `404` when the
order has no quotation yet or belongs to another customer.

### 4. Invoice lifecycle

The generated invoice runs on its own `InvoiceStatus` lifecycle
(`InvoiceLifecycleService`), which has **no** `delivered` state. Approving or
delivering the order does **not** change the invoice status — the admin
approves it separately. This keeps the two state machines independent.

### 5. Ownership

Every customer-facing order, quotation, and invoice endpoint is scoped by
`customer_id`. Requests for another customer's record return `404`. Session
auth is enforced by the `check.customer.session` middleware; the APK proxy
routes that used to serve customer data without a session were moved behind
it.

### Verified end to end

Both scripts run inside a transaction that is rolled back, so they leave no
data behind:

- `php artisan tinker --execute="require base_path('e2e_order_flow.php');"`
  walks all ten states and asserts the side effects at each step.
- `php artisan tinker --execute="require base_path('http_order_test.php');"`
  drives the real HTTP kernel: 32 customer routes, auth blocking, checkout,
  the response contract, IDOR checks, and all three PDF endpoints.

---

## Key Files

- `app/Enums/OrderStatus.php` — Status definitions and labels
- `app/Services/OrderLifecycleService.php` — State machine transitions and side effects
- `app/Services/OrderStepPresenter.php` — Shared steps, progress, timeline, and response permission
- `app/Services/OrderService.php` — Order creation and stock reservation
- `app/Services/QuotationService.php` — Quotation numbering and customer responses
- `app/Services/InvoiceLifecycleService.php` — Invoice approval workflow
- `app/Http/Middleware/CheckCustomerSession.php` — Session guard for customer routes
- `app/Http/Controllers/Customer/CustomerOrderController.php` — Customer checkout, history, details, response, PDFs
- `app/Http/Controllers/Customer/CustomerProfileController.php` — Profile, favorites, notifications
- `app/Http/Controllers/Admin/OrderController.php` — Admin order CRUD
- `resources/views/app_android/more/order_history.blade.php` — Customer order list
- `resources/views/app_android/more/order_details.blade.php` — Customer order detail with steps
- `resources/views/dashboard/orders/partials/order-steps.blade.php` — Admin view of the same steps
- `routes/web.php` — Customer route group and session middleware boundary