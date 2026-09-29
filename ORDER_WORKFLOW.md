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

## Key Files

- `app/Enums/OrderStatus.php` — Status definitions and labels
- `app/Services/OrderLifecycleService.php` — State machine transitions and side effects
- `app/Services/OrderService.php` — Order creation and quotation generation
- `app/Services/InvoiceLifecycleService.php` — Invoice approval workflow
- `app/Http/Controllers/Admin/OrderController.php` — Admin order CRUD
- `app/Http/Controllers/Customer/CustomerOrderController.php` — Customer checkout
- `app/Http/Controllers/Customer/CustomerAppController.php` — Alternative customer checkout