# SERVICE CONTRACTS v3
*Last Updated: 2026-09-17*

## Changes from v1 → v2 → v3

| Feature | v1 | v2 | v3 |
|---|---|---|---|
| Num Services | 8 | 9 (missing Delivery/Return/PaymentDeadline) | 11 (all included) |
| PriceResult | price, discount, finalPrice, source, priceListItemId, currencyId, exchangeRate | Removed currencyId; added unitCost — **broken** | Full restoration: unitPrice, discount, finalUnitPrice, taxPercent, taxAmount, source, priceListItemId, currencyId, exchangeRate |
| PriceSource | customer_price_list, default_price_list, base, manual | base, price_list, manual, wholesale — **lost distinction** | Restored: CustomerPriceList, DefaultPriceList, Base, Manual (removed wholesale) |
| DTOs | Tables only | Tables only | **All as PHP readonly classes** |
| Transition Table | Correct | Broken format | ✅ From \| To \| Side Effects |
| Tax Fields | Not documented | Referenced but no migration | ✅ Migration 2026_09_17_100025 created |
| QuotedLine DTO | Not defined | Referenced, not defined | ✅ Full definition |
| InvoiceService | Not split | Split but not documented | ✅ Split + documented boundaries |
| calculatePrice $date | Not included | Not included | ✅ Added `?Carbon $date = null` for time-sensitive pricing |
| ItemPack ordering | Used `sequence` | Not specified | ✅ Uses `sort_order` (actual column) |
| PriceList active flag | Used `is_active` | Not specified | ✅ Uses `status = 'active'` (actual column) |
| Currency ID resolution | Used `default_currency_id` | Not specified | ✅ Uses `base_currency` flag on Currency |
| convertToCurrency | Added but unused | Not specified | ✅ Removed (not needed, rate=1.0 pending CurrencyService) |
| Driver/User Model | Ambiguous (Driver) | Used Driver (non-existent) | ✅ Uses User — system driver is User |
| DeliveryStatus Enum | — | Used DeliveryStatus (non-existent) | ✅ Uses DeliveryStage (existing enum) |
| Return Model | Return | Used Return (non-existent) | ✅ Uses ReturnRequest (correct model name) |
| ReturnResult DTO | — | Fatal PHP default: `new Collection` in param | ✅ Fixed to `?Collection $items = null` |
| approve() Delivery | Unclear | Delivery created via Event | ✅ Direct creation inside approve() |
| Changes section | Not requested | Not provided | ✅ Full comparison table v1→v2→v3 |

---

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

## DTO Definitions (PHP readonly classes)

### PriceResult

```php
<?php
namespace App\DTOs;

use App\Enums\PriceSource;

final readonly class PriceResult {
    public function __construct(
        public float $unitPrice,
        public float $discount,
        public float $finalUnitPrice,
        public float $taxPercent,
        public float $taxAmount,
        public PriceSource $source,
        public ?int $priceListItemId,
        public int $currencyId,
        public float $exchangeRate,
    ) {}
}
```

### AvailabilityResult

```php
<?php
namespace App\DTOs;

final readonly class AvailabilityResult {
    public function __construct(
        public int $itemId,
        public int $itemPackId,
        public int $warehouseId,
        public float $onHand,
        public float $reserved,
        public float $available,
        public float $damaged,
        public float $reorderLevel,
        public bool $needsReorder,
    ) {}
}
```

### FefoAllocation

```php
<?php
namespace App\DTOs;

final readonly class FefoAllocation {
    public function __construct(
        public int $batchStockId,
        public int $batchItemId,
        public float $quantity,
        public ?string $expiryDate,
        public ?float $unitCost,
    ) {}
}
```

### CommitResult

```php
<?php
namespace App\DTOs;

final readonly class CommitResult {
    public function __construct(
        public array $allocations,   // array<int, FefoAllocation>
        public float $totalQuantity,
        public int $invoiceId,
    ) {}
}
```

### WalletResult

```php
<?php
namespace App\DTOs;

use App\Enums\WalletTransactionType;

final readonly class WalletResult {
    public function __construct(
        public float $previousBalance,
        public float $newBalance,
        public float $amount,
        public WalletTransactionType $type,
        public ?int $walletId = null,
    ) {}
}
```

### TransitionResult

```php
<?php
namespace App\DTOs;

final readonly class TransitionResult {
    public function __construct(
        public string $fromStatus,
        public string $toStatus,
        public array $sideEffects = [],
        public bool $success = true,
        public ?string $errorMessage = null,
    ) {}
}
```

### DeliveryResult

```php
<?php
namespace App\DTOs;

use App\Enums\DeliveryStage;
use App\Models\User;

final readonly class DeliveryResult {
    public function __construct(
        public DeliveryStage $stage,
        public ?User $driver = null,
        public ?\Carbon\Carbon $estimatedArrival = null,
        public float $distanceKm = 0.0,
    ) {}
}
```

### ReturnResult

```php
<?php
namespace App\DTOs;

use Illuminate\Support\Collection;

final readonly class ReturnResult {
    public function __construct(
        public bool $canReturn,
        public ?string $reason = null,
        public ?Collection $items = null,
        public ?int $orderId = null,
    ) {}
}
```

### QuotedLine

```php
<?php
namespace App\DTOs;

use App\Enums\PriceSource;

final readonly class QuotedLine {
    public function __construct(
        public int $itemId,
        public ?int $itemPackId,
        public float $quantity,
        public float $unitPrice,
        public float $discount,
        public float $finalUnitPrice,
        public float $taxPercent,
        public float $taxAmount,
        public float $subtotal,
        public float $total,
        public ?int $priceListItemId,
        public PriceSource $source,
        public int $currencyId,
        public float $exchangeRate,
    ) {}
}
```

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
        ?Carbon $date = null,
    ): PriceResult;

    public function getPrice(
        Customer $customer,
        int $itemId,
        int $itemPackId,
        float $quantity,
        ?Carbon $date = null,
        ?int $currencyId = null,
    ): PriceResult;

    public function bulkQuote(Customer $customer, array $items): BulkQuoteResult;

    public function resolveCustomerPrice(
        Customer $customer,
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array;

    public function resolveDefaultPriceListPrice(
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array;
}
```

**PriceSource Enum**: `CustomerPriceList`, `DefaultPriceList`, `Base`, `Manual`

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

## 2. InventoryServiceInterface

**File:** `app/Contracts/InventoryServiceInterface.php`

```php
interface InventoryServiceInterface {
    public function checkAvailability(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): AvailabilityResult;

    public function reserve(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        string $referenceType,
        int $referenceId,
        ?Carbon $expiresAt = null,
    ): StockReservation;

    public function release(int $reservationId, ?string $reason = null): void;

    public function commit(Invoice $invoice): CommitResult;

    public function expireReservations(): int;

    public function getAvailable(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
    ): float;

    public function allocateFefo(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
    ): array;

    public function adjust(
        int $inventoryId,
        float $newQuantity,
        string $reason,
        int $userId,
    ): void;

    public function receiveGoods(
        int $itemId,
        ?int $itemPackId,
        int $warehouseId,
        float $quantity,
        ?int $batchItemId = null,
        ?int $userId = null,
    ): void;

    public function receiveReturn(ReturnItem $returnItem): void;
    public function reverseCommit(Invoice $invoice): void;
}
```

### Return & CommitResult Signatures

- `commit(Invoice): CommitResult` — returns `CommitResult` with `allocations`, `totalQuantity`, `invoiceId`
- `allocateFefo`: returns `array<int, FefoAllocation>` sorted by `expiry_date ASC` (FEFO = First Expired, First Out)

### StockReservationStatus Enum

`StockReservationStatus`: `Active`, `Consumed`, `Released`, `Expired` (mirrors DB CHECK constraint)

### FEFO Allocation Strategy

1. Query `batch_item_stocks` joined with `batch_items`, filtered by `quantity > 0`
2. Order by `bi.expiry_date ASC` (oldest lots first)
3. Allocate quantity from each lot until fulfilled
4. During `commit()`, `allocateFefoLocked()` uses `lockForUpdate()` to prevent race conditions

### Inventory Movement Types

`InventoryMovementType` enum: `In`, `Out`, `Reserve`, `Release`, `Adjustment`, `ReturnGood`, `ReturnDamaged`

### TTL & Reservation Policy

**Reservation TTL** defaults to 24 hours (set in `reserve()` via `$expiresAt ?? now()->addHours(24)`).

**Reservation lifecycle:**
1. `reserve()` creates a `StockReservation` with `status = Active`, `expires_at = now() + TTL`
2. Reservation is locked via `lockForUpdate()` during reserve/commit operations
3. `expireReservations()` finds all `Active` reservations where `expires_at < now()` and transitions them to `Released` (restocking reserved quantity)
4. Once committed via `commit(Invoice)`, reservation `status` becomes `Consumed` and cannot be released

**BatchItemStock locking:** All `reserve`, `release`, `commit` operations use `lockForUpdate()` on `BatchItemStock` records.

### Commit Flow (for Invoice approval)

1. Check for existing batch links (idempotency guard)
2. For each invoice item, call `allocateFefoLocked()` to get FEFO allocations
3. Decrement `batch_item_stocks.quantity` and `inventory.quantity_on_hand` for each allocation
4. Create `InventoryMovement` records (type = `Out`)
5. Create `InvoiceItemBatch` link records
6. Decrement `reserved_quantity` on inventory
7. Transition matching `StockReservation` from `Active` → `Consumed`
8. `Log::warning` emitted if no reservation found for an invoice item (non-fatal)

### Reverse Commit Flow

1. Find all `InvoiceItemBatch` links for the invoice
2. Restore `batch_item_stocks.quantity` and `inventory.quantity_on_hand`
3. Create adjustment `InventoryMovement` records
4. Delete `InvoiceItemBatch` links
5. Transition any remaining `Active` reservations to `Released`

---

## 3. WalletServiceInterface

**File:** `app/Contracts/WalletServiceInterface.php`

```php
interface WalletServiceInterface {
    public function deposit(
        Customer $customer,
        float $amount,
        ?PaymentMethod $method = null,
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

---

## 4. OrderServiceInterface

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

## 5. OrderLifecycleServiceInterface

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
| admin_edited | quotation_sent | InventoryService::reserve() per line item |
| quotation_sent | customer_approved | Create Invoice draft (pending_approval) |
| quotation_sent | customer_rejected | → cancelled (auto) → release reservations |
| quotation_sent | customer_requested_changes | → admin_edited → release reservations |
| customer_approved | invoice_generated | InvoiceService::generateFromOrder() |
| invoice_generated | pending_approval | AuditLog only |
| pending_approval | approved | InvoiceLifecycleService::approve() — commit stock, create delivery |
| approved | in_delivery | DeliveryService::assignDriver() |
| in_delivery | delivered | Order.status=delivered + notify |
| any | cancelled | Release reservations if active |
| any | returned | Initiate return workflow |
| any | expired | Release reservations |

---

## 6. InvoiceServiceInterface

**File:** `app/Contracts/InvoiceServiceInterface.php`

```php
interface InvoiceServiceInterface {
    public function generateFromOrder(Order $order, ?User $user = null): Invoice;
    public function void(Invoice $invoice, ?string $reason = null, ?User $user = null): Invoice;
    public function cancel(Invoice $invoice, ?string $reason = null, ?User $user = null): Invoice;
    public function markAsPartiallyPaid(Invoice $invoice, float $amount, ?User $user = null): Invoice;
    public function markAsOverdue(Invoice $invoice, ?User $user = null): Invoice;
    public function duplicate(Invoice $invoice, ?User $user = null): Invoice;
}
```

### Service Boundaries: InvoiceService vs InvoiceLifecycleService

| Responsibility | InvoiceService | InvoiceLifecycleService |
|---|---|---|
| Create / Generate | ✅ generateFromOrder | ❌ |
| State Transitions | ❌ | ✅ approve, reject, canTransitionTo |
| Cancel / Void | ✅ cancel, void | ❌ |
| Duplicate | ✅ duplicate | ❌ |
| Mark Paid/Overdue | ✅ markAsPartiallyPaid, markAsOverdue | ❌ |
| Commit Stock + Create Delivery | ❌ (done via approve side effect) | ✅ (inside approve()) |

---

## 7. InvoiceLifecycleServiceInterface

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
1. `InventoryService::commit($invoice)` — convert reservations from `active` to `committed`
2. `OrderLifecycleService::transition($order, OrderStatus::Approved)` — update order status
3. `DeliveryService::createFromInvoice($invoice)` — create Delivery directly (not via Event Listener)
4. Dispatch `InvoiceApproved` event — Listeners may react (e.g., send notification)
5. Register `AuditLog` entry

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

`bulkQuote` returns `Collection<QuotedLine>` (typed DTO collection).

---

## 10. DeliveryServiceInterface

**File:** `app/Contracts/DeliveryServiceInterface.php`

```php
interface DeliveryServiceInterface {
    public function assignDriver(Delivery $delivery, User $driver): Delivery;
    public function updateStage(Delivery $delivery, DeliveryStage $stage, ?string $notes = null): Delivery;
    public function cancel(Delivery $delivery, ?string $reason = null, ?User $user = null): Delivery;
    public function createFromInvoice(Invoice $invoice, ?User $user = null): Delivery;
    public function getDeliveries(Order $order): Collection;
}
```

**Notes:**
- Uses `User` (not `Driver`) — drivers are Users in the system
- Uses `DeliveryStage` enum (not `DeliveryStatus`)
- `cancel()` performs soft-cancel via `status = cancelled` + `AuditLog`

---

## 11. ReturnServiceInterface

**File:** `app/Contracts/ReturnServiceInterface.php`

```php
interface ReturnServiceInterface {
    public function validateReturn(Invoice $invoice): ReturnValidationResult;
    public function processReturn(
        Invoice $invoice,
        array $items,
        ?string $reason = null,
        ?User $user = null,
    ): ReturnRequest;
    public function updateReturnStatus(ReturnRequest $return, ReturnStatus $newStatus, ?string $notes = null): ReturnRequest;
}
```

---

## 12. PaymentDeadlineServiceInterface

**File:** `app/Contracts/PaymentDeadlineServiceInterface.php`

```php
interface PaymentDeadlineServiceInterface {
    public function calculateDeadline(Invoice $invoice): Carbon;
    public function isOverdue(Invoice $invoice): bool;
    public function extendDeadline(Invoice $invoice, int $days, ?string $reason = null): Invoice;
    public function getOverdueInvoices(?Carbon $asOf = null): Collection;
}
```

---

## Migration List (Updated)

| Date | Migration | Purpose |
|---|---|---|
| 2026_09_17_095000 | `create_core_tables.php` | Base schema: customers, items, invoices, etc. |
| 2026_09_17_095500 | `create_price_lists_table.php` | Customer price lists |
| 2026_09_17_100000 | `create_wallets_table.php` | CustomerWallet, WalletTransaction |
| 2026_09_17_100010 | `create_inventory_tables.php` | Batches, reservations, warehouse stocks |
| **2026_09_17_100025** | **add_tax_fields_to_order_items** | **Adds tax_percent, tax_amount to order_items, quotation_items, invoice_items** |
| 2026_09_17_100030 | `create_deliveries_table.php` | Deliveries, delivery stages history |
| 2026_09_17_100040 | `create_returns_table.php` | Return requests, return items |
| 2026_09_17_100050 | `create_payments_table.php` | Payments, payment methods |
| 2026_09_17_140000 | `create_stock_reservations_table.php` | StockReservation model (item_id, item_pack_id, warehouse_id, reserved_for_type/id, quantity, status, expires_at) |
| 2026_09_17_140500 | `create_inventory_movements_table.php` | InventoryMovement model (inventory_id, item_id, warehouse_id, type, quantity, batch_item_id, balance_after, notes) |
| 2026_09_17_141000 | `create_invoice_item_batches_table.php` | InvoiceItemBatch model (invoice_item_id, batch_item_id, quantity, unit_cost) |
