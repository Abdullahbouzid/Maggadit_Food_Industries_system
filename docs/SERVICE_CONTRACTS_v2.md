# SERVICE CONTRACTS v2
*Last Updated: 2026-09-17*

## Transaction & Event Strategy

| Operation Type | Transaction Wrapper | Event Timing |
|---|---|---|
| Financial (wallet debit/deposit, payment record) | `UnitOfWork::transactional()` | Registered inside closure, dispatched after `commit` |
| Inventory (reserve/release/commit) | `UnitOfWork::transactional()` | Registered inside closure, dispatched after `commit` |
| Status transitions (order/invoice/delivery) | `DB::transaction()` | Dispatched after `commit` via `OrderStatusChanged` event |
| Audit logging | Within parent transaction | No standalone events |
| Reads (getBalance, checkAvailability) | None | N/A |

**Rule:** Events are registered inside the transaction closure via `$registerEvent(callable)`.
They are batched and dispatched only after the transaction commits successfully.
Events that don't modify state and don't need transactional guarantees are dispatched directly.

---

## 1. PricingServiceInterface

**File:** `app/Contracts/PricingServiceInterface.php`

```php
interface PricingServiceInterface {
    public function calculatePrice(
        Item $item,
        Customer $customer,
        float $quantity = 1.0,
        ?ItemPack $itemPack = null,
    ): PriceResult;
}
```

**DTO:** `App\DTOs\PriceResult` (readonly class)
| Field | Type |
|---|---|
| `unitPrice` | float |
| `unitCost` | float\|null |
| `discountAmount` | float\|null |
| `taxAmount` | float\|null |
| `taxPercent` | float\|null |
| `subtotal` | float |
| `total` | float |
| `priceListId` | int\|null |
| `source` | PriceSource (Enum) |

**PriceSource Enum:**
| Value | Description |
|---|---|
| `base` | Item base price from item definition |
| `price_list` | Customer-specific price list |
| `manual` | Manually overridden on order line |
| `wholesale` | Wholesale tier price |

---

## 2. InventoryServiceInterface

**File:** `app/Contracts/InventoryServiceInterface.php`

```php
interface InventoryServiceInterface {
    public function checkAvailability(
        int $itemId,
        int $warehouseId,
        float $quantity = 1.0,
        ?int $itemPackId = null,
    ): AvailabilityResult;

    public function reserve(
        int $itemId,
        int $warehouseId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        ?int $itemPackId = null,
        ?Carbon $expiresAt = null,
    ): StockReservation;

    public function release(int $reservationId, ?string $reason = null): void;

    public function commit(Invoice $invoice): void;

    public function expireReservations(int $ttlSeconds = 3600): int;
}
```

### TTL & Reservation Policy

**Reservation TTL** is controlled by config:
- `config('inventory.reservation_ttl_seconds')` — default: `3600` (1 hour)

**Reservation lifecycle:**
1. `reserve()` creates a `StockReservation` with `status = active`, `expires_at = now() + TTL`
2. Reservation is locked via `lockForUpdate()` during reserve/commit operations
3. `expireReservations()` finds all `active` reservations where `expires_at < now()` and transitions them to `expired`
4. Once committed via `commit(Invoice)`, reservation `status` becomes `committed` and cannot be released

**BatchItemStock locking:** All `reserve`, `release`, `commit` operations use `lockForUpdate()` on `BatchItemStock` records.

**DTO:** `App\DTOs\AvailabilityResult` (readonly class)
| Field | Type |
|---|---|
| `isAvailable` | bool |
| `available` | float |
| `requested` | float |
| `unitId` | int\|null |
| `batchId` | int\|null |

---

## 3. WalletServiceInterface

**File:** `app/Contracts/WalletServiceInterface.php`

```php
interface WalletServiceInterface {
    public function deposit(
        Customer $customer,
        float $amount,
        ?string $method = null,
        ?string $referenceNumber = null,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction;

    public function debit(
        Customer $customer,
        float $amount,
        Invoice $invoice,
        ?string $notes = null,
        ?int $userId = null,
    ): WalletTransaction;

    public function refund(
        Customer $customer,
        float $amount,
        CreditNote $creditNote,
        ?int $userId = null,
        ?string $notes = null,
    ): WalletTransaction;

    public function adjust(
        Customer $customer,
        float $newBalance,
        string $reason,
        int $userId,
    ): WalletTransaction;

    public function getBalance(Customer $customer, int $currencyId): float;

    public function hasSufficientBalance(Customer $customer, float $amount, int $currencyId): bool;
}
```

### Tax Handling Strategy

**Tax is computed per-item** by `PricingService::calculatePrice()`.

- `OrderItem.tax_percent` — percentage applied per line
- `OrderItem.tax_amount` — calculated per line as `subtotal * tax_percent / 100`
- Tax is based on the customer's tax group and the item's tax class
- Invoice-level totals are aggregated from item-level calculations:
  - `Invoice.subtotal` = SUM of `OrderItem.subtotal`
  - `Invoice.tax_total` = SUM of `OrderItem.tax_amount`
  - `Invoice.total` = `subtotal + tax_total - discount_total`

---

## 4. InvoiceServiceInterface

**File:** `app/Contracts/InvoiceServiceInterface.php`

```php
interface InvoiceServiceInterface {
    public function generateFromOrder(Order $order, ?User $user = null): Invoice;
    public function void(Invoice $invoice, ?string $reason = null, ?User $user = null): Invoice;
    public function cancel(Invoice $invoice, ?string $reason = null, ?User $user = null): Invoice;
    public function markAsPaid(Invoice $invoice, ?User $user = null): Invoice;
    public function markAsPartiallyPaid(Invoice $invoice, float $amount, ?User $user = null): Invoice;
    public function markAsOverdue(Invoice $invoice, ?User $user = null): Invoice;
    public function duplicate(Invoice $invoice, ?User $user = null): Invoice;
}
```

---

## 5. InvoiceLifecycleServiceInterface

**File:** `app/Contracts/InvoiceLifecycleServiceInterface.php`

```php
interface InvoiceLifecycleServiceInterface {
    public function approve(Invoice $invoice, ?User $user = null, ?string $notes = null): Invoice;
    public function reject(Invoice $invoice, ?string $reason = null, ?User $user = null): Invoice;
    public function canTransitionTo(Invoice $invoice, InvoiceStatus $newStatus): bool;
    public function getAllowedTransitions(Invoice $invoice): array;
}
```

**approve() Side Effects (in order):**
1. `InventoryService::commit($invoice)` — consumes reservations
2. `OrderLifecycleService::transition($order, OrderStatus::Approved)` — updates order status
3. Creates `Delivery` with status `pending_assignment`
4. Dispatches `InvoiceApproved` event
5. Registers `AuditLog` entry

---

## 6. OrderLifecycleServiceInterface

**File:** `app/Contracts/OrderLifecycleServiceInterface.php`

```php
interface OrderLifecycleServiceInterface {
    public function transition(
        Order $order,
        OrderStatus $newStatus,
        ?User $user = null,
        ?string $notes = null,
        bool $fromSystem = false,
    ): Order;

    public function canTransitionTo(Order $order, OrderStatus $newStatus): bool;
    public function getAllowedTransitions(Order $order): array;
    public function markAsInvoiceGenerated(Order $order, ?User $user = null): Order;
    public function markAsInDelivery(Order $order, ?User $user = null): Order;
}
```

### Order Status Transition Table

| From | To | Side Effects |
|---|---|---|
| new | pending_review | AuditLog only |
| pending_review | admin_edited | AuditLog only |
| admin_edited → quotation_sent | quotation_sent | `InventoryService::reserve()` per line item |
| quotation_sent → customer_approved | customer_approved | Create Invoice draft (`pending_approval`) |
| quotation_sent → customer_rejected | cancelled | `InventoryService::release()` all reservations |
| quotation_sent → customer_requested_changes | admin_edited | `InventoryService::release()` all reservations |
| customer_approved | invoice_generated | `InvoiceService::generateFromOrder()` |
| invoice_generated | pending_approval | AuditLog only |
| pending_approval → approved | approved | `InvoiceLifecycleService::approve()` |
| approved → in_delivery | in_delivery | `DeliveryService::assignDriver()` |
| in_delivery → delivered | delivered | Update order status, notify customer |
| Any → cancelled | cancelled | `InventoryService::release()` if reservations active |
| Any → returned | returned | Initiates return workflow |
| Any → expired | expired | `InventoryService::release()` |

---

## 7. OrderServiceInterface

**File:** `app/Contracts/OrderServiceInterface.php`

```php
interface OrderServiceInterface {
    public function create(
        Customer $customer,
        array $items,
        ?string $deliveryAddress = null,
        ?string $deliveryPhone = null,
        ?Carbon $scheduledDeliveryDate = null,
        ?User $user = null,
    ): Order;

    public function addItem(Order $order, array $itemData, ?User $user = null): OrderItem;
    public function updateItem(Order $order, OrderItem $orderItem, array $data): OrderItem;
    public function cancel(Order $order, ?string $reason = null, ?User $user = null): Order;
}
```

**Responsibility split:**
- `OrderService::create()` calls `InventoryService::checkAvailability()` (read-only check)
- `OrderLifecycleService::transition()` to `quotation_sent` calls `InventoryService::reserve()` (state-changing)

---

## 8. PaymentServiceInterface

**File:** `app/Contracts/PaymentServiceInterface.php`

```php
interface PaymentServiceInterface {
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        PaymentMethod $method,
        ?int $userId = null,
        ?string $referenceNumber = null,
    ): Payment;

    public function getPaymentMethods(): Collection;
    public function refund(Payment $payment, ?float $amount = null, ?string $reason = null): Payment;
    public function applyPartialPayment(Invoice $invoice, float $amount, PaymentMethod $method): void;
}
```

---

## 9. QuotationServiceInterface

**File:** `app/Contracts/QuotationServiceInterface.php`

```php
interface QuotationServiceInterface {
    public function quote(Order $order, ?int $priceListId = null): QuotedOrder;
    public function bulkQuote(Collection $orders): Collection<QuotedLine>;
    public function acceptQuote(Quote $quote): Quote;
    public function rejectQuote(Quote $quote, ?string $reason = null): Quote;
}
```

`bulkQuote` returns `Collection<QuotedLine>` (typed DTO collection, not `Collection<array>`).
