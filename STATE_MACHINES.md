# State Machines — Maggadit Food Industries System

---

## 🔄 1. Order State Machine

**Model:** `Order`  
**States:**

```
new
  └─→ pending_review (Admin creates from customer order or internal)
        └─→ admin_edited (Admin modifies quotation)
              └─→ quotation_sent (Quotation sent to customer)
                    ├─→ customer_approved
                    │     └─→ invoice_generated (Quotation → Invoice)
                    │           └─→ pending_approval (Invoice pending approval)
                    ├─→ customer_rejected
                    │     └─→ cancelled
                    └─→ customer_requested_changes
                          └─→ admin_edited (Loop)
```

**Transition Rules:**

| From State | Allowed Transitions | Trigger |
|------------|-------------------|---------|
| `new` | `pending_review` | Admin action |
| `pending_review` | `admin_edited` | Admin edits |
| `pending_review` | `cancelled` | Admin cancels |
| `admin_edited` | `quotation_sent` | Send quotation |
| `quotation_sent` | `customer_approved` | Customer approves |
| `quotation_sent` | `customer_rejected` | Customer rejects |
| `quotation_sent` | `customer_requested_changes` | Customer requests changes |
| `customer_approved` | `invoice_generated` | System generates invoice |
| `customer_rejected` | `cancelled` | Auto |
| `customer_requested_changes` | `admin_edited` | Admin re-edits |
| `invoice_generated` | `pending_approval` | Invoice created |
| `pending_approval` | `approved` | Admin approves |
| `pending_approval` | `rejected` | Admin rejects |
| `approved` | `in_delivery` | Delivery assigned |
| `approved` | `cancelled` | Cancellation |
| `in_delivery` | `delivered` | Driver confirms |
| `in_delivery` | `returned` | Customer returns |
| `in_delivery` | `cancelled` | Cancellation |

---

## 🔄 2. Invoice State Machine

**Model:** `Invoice`  
**States:**

```
pending_approval
  ├─→ approved (cash or credit)
  │     ├─→ paid (full payment)
  │     ├─→ partially_paid (0 < paid < total)
  │     ├─→ expired (deadline passed, cash payment)
  │     └─→ cancelled
  ├─→ rejected
  │     └─→ cancelled
  └─→ cancelled
```

**Transition Rules:**

| From State | Allowed Transitions | Trigger | Conditions |
|------------|-------------------|---------|------------|
| `pending_approval` | `approved` | Admin approves | `payment_type = cash` → sets `payment_deadline_at`; `payment_type = credit` → sets `due_date` |
| `pending_approval` | `rejected` | Admin rejects | Any reason |
| `approved` | `paid` | Payment received | `paid_amount == total_amount` |
| `approved` | `partially_paid` | Partial payment | `0 < paid_amount < total_amount` |
| `approved` | `expired` | Deadline scheduler | `payment_type = cash` AND `payment_deadline_at < now()` AND `paid_amount < total_amount` |
| `approved` | `cancelled` | Admin/System | Any reason |
| `rejected` | `cancelled` | Auto | Upon rejection |
| `partially_paid` | `paid` | Remaining payment | `paid_amount == total_amount` |
| `partially_paid` | `expired` | Deadline scheduler | `payment_type = cash` AND deadline passed |
| `paid` | `refunded` | Return processed | CreditNote issued |
| `paid` | `cancelled` | Full return | Full return → refund |

**Important Business Rules:**
- `approved` state for cash: `payment_deadline_at = now() + PaymentDeadlineSetting.hours_limit`
- `approved` state for credit: `due_date` set based on `Customer.payment_terms_days`
- `expired` triggers: StockReservation release + Inventory reverse commit + notification
- `paid` state = Invoice marked complete, can trigger Delivery
- Status transition requires AuditLog entry

---

## 🔄 3. Delivery State Machine

**Model:** `Delivery`  
**States:**

```
shipping
  └─→ out_for_delivery
        └─→ unloading
              └─→ received
```

**Transition Rules:**

| From State | Allowed Transitions | Trigger |
|------------|-------------------|---------|
| `shipping` | `out_for_delivery` | Driver starts route |
| `out_for_delivery` | `unloading` | Driver arrives at destination |
| `unloading` | `received` | Customer signs/accepts |
| Any | `cancelled` | Admin/Driver cancellation |

**DeliveryStageLog:**
- Each stage transition creates a `DeliveryStageLog` record with:
  - `delivery_id`, `stage`, `updated_by`, `latitude`, `longitude`, `notes`, `photo_path`, `created_at`

---

## 🔄 4. ReturnRequest State Machine

**Model:** `ReturnRequest`  
**States:**

```
pending
  ├─→ approved → processed
  └─→ rejected
```

**Transition Rules:**

| From State | Allowed Transitions | Trigger | Notes |
|------------|-------------------|---------|-------|
| `pending` | `approved` | Admin review | Sets `reviewed_by`, `reviewed_at` |
| `pending` | `rejected` | Admin review | Sets `reviewed_by`, `reviewed_at` |
| `approved` | `processed` | Return execution | Creates CreditNote, Inventory return |
| `rejected` | (terminal) | - | No further transitions |

**ReturnItem:**
- Each `ReturnItem` has: `invoice_item_id`, `item_id`, `item_pack_id`, `batch_item_id`, `quantity_returned`, `unit_price`, `refund_amount`, `condition`, `notes`
- `condition`: `good` | `damaged`
- If `condition = damaged`: Stock goes to damaged inventory
- If `condition = good`: Stock returns to available inventory

---

## 🔄 5. Inventory State Machine

**Model:** `Inventory`  
**No explicit states** — managed via `InventoryMovement` types.

**Movement Types:**

```
in          → Receiving stock into warehouse
out         → Shipping stock out of warehouse
reserve     → Pre-reserving stock for an order
release     → Releasing a reservation (cancellation/expiry)
adjustment  → Manual adjustment (count/audit)
damage      → Damaged goods
return_good → Returned item in good condition
return_damaged → Returned item damaged
```

**Movement Rules:**

| Movement Type | Inventory Impact | Quantity Sign |
|---------------|-----------------|---------------|
| `in` | `quantity_on_hand += qty` | Positive |
| `out` | `quantity_on_hand -= qty` | Negative |
| `reserve` | `reserved_quantity += qty` | Positive (reserved) |
| `release` | `reserved_quantity -= qty` | Negative (released) |
| `adjustment` | `quantity_on_hand = new_qty` | Signed diff |
| `damage` | `damaged_quantity += qty`, `quantity_on_hand -= qty` | Negative |
| `return_good` | `quantity_on_hand += qty` | Positive |概述
| `return_damaged` | `damaged_quantity += qty`, `quantity_on_hand -= qty` | Negative |

**FEFO Rules in `out` and `return_good`:**
- `out`: Allocate using `BatchItemStock` ordered by `expiry_date ASC`
- `return_good`: Return to same `BatchItemStock` record (or create new with same expiry)

**Locking Rules:**
- Every Inventory/BatchItemStock modification uses `lockForUpdate()` inside `DB::transaction`
- `InventoryMovement` record created for every change
- `balance_after` stored in movement record

---

## 🔄 6. Quotation State Machine

**Model:** `Quotation`  
**States:**

```
draft
  └─→ sent (via Order)
        ├─→ customer_approved
        ├─→ customer_rejected
        └─→ customer_requested_changes
```

**Note:** Quotation is linked to `Order` via `order_id`. Its status is managed through the Order's lifecycle. The `Quotation` model stores the snapshot data.

---

## 🔄 7. Wallet State Machine

**Model:** `CustomerWallet`  
**Balance invariant:** `balance >= 0` unless `customer_type = credit`

**Transaction Types:**

```
deposit      → balance += amount (Admin credit)
purchase     → balance -= amount (Invoice paid from wallet)
refund       → balance += amount (Return/CreditNote refund)
withdrawal   → balance -= amount (Admin debit)
adjustment   → balance = new_balance (Manual correction)
```

**Wallet Rules:**
- `purchase`: Requires `balance >= amount` unless `customer_type = credit`
- `withdrawal`: Requires `balance >= amount` (unless admin override)
- Every transaction creates `WalletTransaction` + `AuditLog`
- Balance updated atomically with `lockForUpdate()`

---

## 🔄 8. InventoryReservation Expiry State Machine

**Model:** `StockReservation`  
**States:**

```
active
  ├─→ consumed (Invoice approved, stock committed)
  ├─→ released (Order cancelled / deadline expired)
  └─→ expired (Reservation TTL exceeded, auto-release)
```

**Rules:**
- `active`: Reservation holds stock, cannot be sold
- `consumed`: Linked invoice approved → Stock deducted from `quantity_on_hand` and `reserved_quantity`
- `released`: Order cancelled or deadline exceeded → `reserved_quantity -= qty`
- `expired`: Scheduler detects `expires_at < now()` → Auto-releases + InventoryMovement `adjustment`
- Scheduler runs every hour (`reservations:release-expired`)

---

## 🔄 9. PaymentDeadline Expiry State Machine

**Model:** `Invoice`  
**Trigger:** Scheduled command `invoices:expire-unpaid` (hourly)

**Rules:**
1. Find: `status = approved` AND `payment_type = cash` AND `payment_deadline_at < now()` AND `paid_amount < total_amount`
2. Set `status = expired`
3. Release all associated `StockReservation` records → status = `released`
4. Reverse `InventoryMovement` for committed stock (if any)
5. Create `InventoryMovement(type: adjustment)` for reversal
6. Send notification to customer + admin
7. Optionally: extend deadline manually → update `payment_deadline_at`

---

## 🔄 10. InventoryMovement Types

**Movement Types:**

```
in          → Receiving stock into warehouse
out         → Shipping stock out of warehouse
reserve     → Pre-reserving stock for an order
release     → Releasing a reservation (cancellation/expiry)
adjustment  → Manual adjustment (count/audit)
damage      → Damaged goods
return_good → Returned item in good condition
return_damaged → Returned item damaged
```

---

## 📡 Events & Listeners

| Transition | Event Fired | Listener | Notification Channel |
|------------|-------------|----------|---------------------|
| Order created | `OrderCreated` | - | - |
| Order status changed | `OrderStatusChanged` | `SendOrderStatusChangedNotification` | Push (FCM) → Customer |
| Quotation sent | `QuotationSent` | - | - |
| Quotation approved | `QuotationApproved` | - | - |
| Invoice approved | `InvoiceApproved` | `SendInvoiceApprovedNotification` | Push (FCM) → Customer |
| Invoice paid | - | - | - |
| Invoice expired | `InvoiceExpired` | `SendInvoiceExpiredNotification` | Push (FCM) → Customer |
| Stock reserved | - | - | - |
| Stock committed | - | - | - |
| Delivery stage changed | `DeliveryStageChanged` | - | - |
| Return requested | `ReturnRequested` | - | - |
| Return processed | `ReturnProcessed` | - | - |
| Wallet deposited | `WalletDeposited` | - | - |
| Wallet debited | `WalletDebited` | - | - |

---

## State Diagram (Visual Summary)

```
┌─────────────────────────────────────────────────────────────────┐
│                        ORDER LIFECYCLE                          │
│                                                                 │
│  new → pending_review → admin_edited → quotation_sent           │
│                        ↘ cancelled                              │
│                            │                                    │
│                     customer_approved                            │
│                            │                                    │
│                     invoice_generated → pending_approval         │
│                                        │                         │
│                                   approved ──→ in_delivery      │
│                                   (cash)    │                   │
│                                   (credit)  │                   │
│                                        ├──→ paid                │
│                                        ├──→ partially_paid      │
│                                        ├──→ expired             │
│                                        └──→ cancelled           │
│                                                             │
│                                  in_delivery ──→ delivered    │
│                                  in_delivery ──→ returned     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        INVOICE LIFECYCLE                         │
│                                                                 │
│  pending_approval ──→ approved ──→ paid                         │
│       │              │           │                              │
│       ├─→ rejected   ├─→ expired  ├──→ partially_paid ──→ paid │
│       └─→ cancelled  └─→ cancelled                             │
│                                                                 │
│  approved (cash) → payment_deadline_at = now + hours_limit      │
│  approved (credit) → due_date = now + payment_terms_days        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        DELIVERY LIFECYCLE                        │
│                                                                 │
│  shipping → out_for_delivery → unloading → received             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        RETURN LIFECYCLE                          │
│                                                                 │
│  pending ──→ approved ──→ processed                             │
│       └─→ rejected                                             │
│                                                                 │
│  processed → CreditNote created → Wallet credit (if wallet)     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        WALLET LIFECYCLE                          │
│                                                                 │
│  deposit → balance increases                                   │
│  purchase → balance decreases (from wallet)                    │
│  refund → balance increases (return)                           │
│  withdrawal → balance decreases                                │
│  adjustment → balance set manually                             │
│                                                                 │
│  Invariant: balance >= 0 (unless customer_type = credit)       │
└─────────────────────────────────────────────────────────────────┘
```

---

*Generated: 2026-09-17*
