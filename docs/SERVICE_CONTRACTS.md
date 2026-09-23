# Service Contracts — Maggadit Food Industries System

## PHP Standards
- PHP 8.2+ with `declare(strict_types=1)` at the top of every file
- Constructor Promotion + Dependency Injection
- All financial/inventory operations within `DB::transaction()`
- All Inventory / Wallet / BatchItemStock modifications use `lockForUpdate()`
- Custom domain exceptions thrown on error (never return false)
- All operations create AuditLog entries via Observers or manually
- Each Service has an Interface + Implementation (contracts in `app/Contracts/` namespace)

---

## DTOs

### PriceResult
| Field | Type | Description |
|-------|------|-------------|
| `price` | `float` | Base price before discount |
| `discount` | `float` | Discount percentage (e.g. 10.0) |
| `finalPrice` | `float` | Final price after discount |
| `source` | `string` | `customer_price_list`, `default_price_list`, `item_base` |
| `priceListItemId` | `?int` | ID of PriceListItem used (if applicable) |
| `currencyId` | `int` | Currency snapshot |
| `exchangeRate` | `float` | Exchange rate snapshot |

### AvailabilityResult
| Field | Type | Description |
|-------|------|-------------|
| `available` | `float` | Available stock (on_hand - reserved) |
| `reserved` | `float` | Reserved quantity |
| `onHand` | `float` | Total on hand |
| `damaged` | `float` | Damaged quantity |

### FefoAllocation
| Field | Type | Description |
|-------|------|-------------|
| `batchStockId` | `int` | BatchItemStock ID |
| `batchItemId` | `int` | BatchItem ID |
| `quantity` | `float` | Allocated quantity from this batch |
| `expiryDate` | `?string` | Expiry date (YYYY-MM-DD) |

### WalletResult
| Field | Type | Description |
|-------|------|-------------|
| `balanceBefore` | `float` | Balance before operation |
| `balanceAfter` | `float` | Balance after operation |
| `amount` | `float` | Transaction amount |
| `transactionId` | `int` | WalletTransaction ID |

### TransitionResult
| Field | Type | Description |
|-------|------|-------------|
| `succeeded` | `bool` | Whether transition was allowed |
| `oldStatus` | `?OrderStatus/InvoiceStatus` | Previous status |
| `newStatus` | `OrderStatus/InvoiceStatus` | New status |
| `message` | `?string` | Error message if failed |

### DeliveryResult
| Field | Type | Description |
|-------|------|-------------|
| `deliveryId` | `int` | Delivery ID |
| `stage` | `string` | Current stage |
| `timeline` | `array` | Full stage history |

### ReturnResult
| Field | Type | Description |
|-------|------|-------------|
| `returnRequestId` | `int` | Return request ID |
| `creditNoteId` | `?int` | CreditNote ID (if processed) |
| `creditNoteNumber` | `?string` | Credit note number |
| `totalRefund` | `float` | Total refund amount |
| `walletCredited` | `float` | Amount credited to wallet |
| `inventoryReturned` | `array` | Batch items returned |

---

## Domain Exceptions

| Exception | Trigger |
|-----------|---------|
| `InsufficientStockException` | reserve() or commit() called with qty > available |
| `InsufficientBalanceException` | debit() called with amount > wallet balance (non-credit customer) |
| `InvalidTransitionException` | OrderLifecycle::transition() to a non-allowed status |
| `CreditLimitExceededException` | Credit customer exceeds credit_limit |
| `ReservationExpiredException` | Operations on expired reservations |
| `NoPriceAvailableException` | PricingService cannot resolve any price |
| `ItemNotFoundException` | Item/Pack not found during pricing/reservation |

---

## Service Interfaces

| # | Service | Interface | Implementation |
|---|---------|-----------|----------------|
| 1 | PricingService | `PricingServiceInterface` | `PricingService` |
| 2 | InventoryService | `InventoryServiceInterface` | `InventoryService` |
| 3 | WalletService | `WalletServiceInterface` | `WalletService` |
| 4 | OrderLifecycleService | `OrderLifecycleServiceInterface` | `OrderLifecycleService` |
| 5 | DeliveryService | `DeliveryServiceInterface` | `DeliveryService` |
| 6 | ReturnService | `ReturnServiceInterface` | `ReturnService` |
| 7 | PaymentDeadlineService | `PaymentDeadlineServiceInterface` | `PaymentDeadlineService` |
| 8 | InvoiceLifecycleService | `InvoiceLifecycleServiceInterface` | `InvoiceLifecycleService` |

---

## 1. PricingService

**Interface:** `App\Contracts\PricingServiceInterface`

### Method: getPrice
| Property | Value |
|----------|-------|
| **Signature** | `getPrice(Customer $customer, int $itemId, int $itemPackId, float $quantity, ?Carbon $date = null, ?int $currencyId = null): PriceResult` |
| **Returns** | `PriceResult` DTO |
| **Throws** | `ItemNotFoundException`, `NoPriceAvailableException` |
| **Example** | `$pricing->getPrice($customer, 12, 45, 10);` |
| **Side effects** | AuditLog only (if price changed) |

### Method: bulkQuote
| Property | Value |
|----------|-------|
| **Signature** | `bulkQuote(Customer $customer, array $items): Collection` |
| **Returns** | `Collection<int, array>` with keys: `item_id`, `item_pack_id`, `quantity`, `unit_price`, `discount`, `final_unit_price`, `line_total`, `currency_id`, `exchange_rate`, `source`, `price_list_item_id` |
| **Throws** | `ItemNotFoundException` |
| **Example** | `$pricing->bulkQuote($customer, [[...]])` |
| **Side effects** | None |

### Method: resolveCustomerPrice (internal)
| Property | Value |
|----------|-------|
| **Signature** | `resolveCustomerPrice(Customer $customer, Item $item, ItemPack $pack, float $quantity, Carbon $date): ?array` |
| **Returns** | `array|null` with keys: `price`, `discount`, `source`, `priceListItemId` |
| **Throws** | None |

### Method: resolveDefaultPriceListPrice (internal)
| Property | Value |
|----------|-------|
| **Signature** | `resolveDefaultPriceListPrice(Item $item, ItemPack $pack, float $quantity, Carbon $date): ?array` |
| **Returns** | `array|null` |
| **Throws** | None |

### Method: findBestPriceItem (internal)
| Property | Value |
|----------|-------|
| **Signature** | `findBestPriceItem(int $priceListId, Item $item, ItemPack $pack, float $quantity, Carbon $date): ?PriceListItem` |
| **Returns** | `PriceListItem|null` (highest min_quantity ≤ quantity) |
| **Throws** | None |

### Method: resolveCurrencyId (internal)
| Property | Value |
|----------|-------|
| **Signature** | `resolveCurrencyId(Customer $customer): int` |
| **Returns** | `int` |
| **Throws** | None |

---

## 2. InventoryService

**Interface:** `App\Contracts\InventoryServiceInterface`

### Method: reserve
| Property | Value |
|----------|-------|
| **Signature** | `reserve(int $itemId, int $itemPackId, int $warehouseId, float $quantity, string $referenceType, int $referenceId, ?Carbon $expiresAt = null): StockReservation` |
| **Returns** | `StockReservation` |
| **Throws** | `InsufficientStockException` |
| **Example** | `$inv->reserve(12, 45, 3, 10, Order::class, $orderId);` |
| **Side effects** | Creates `StockReservation`, `InventoryMovement(type: reserve)`, updates `Inventory.reserved_quantity` |

### Method: release
| Property | Value |
|----------|-------|
| **Signature** | `release(int $reservationId): void` |
| **Returns** | `void` |
| **Throws** | `\Exception` if reservation not active |
| **Example** | `$inv->release($reservation->id);` |
| **Side effects** | Updates `StockReservation.status` to `released`, creates `InventoryMovement(type: release)` |

### Method: commit
| Property | Value |
|----------|-------|
| **Signature** | `commit(int $reservationId, Invoice $invoice): void` |
| **Returns** | `void` |
| **Throws** | `InsufficientStockException` |
| **Example** | `$inv->commit($reservation->id, $invoice);` |
| **Side effects** | FEFO allocation from `BatchItemStock`, creates `InventoryMovement(type: out)` per batch, creates `invoice_item_batches` records, updates `Inventory.quantity_on_hand` and `reserved_quantity` |

### Method: adjust
| Property | Value |
|----------|-------|
| **Signature** | `adjust(int $inventoryId, float $newQuantity, string $reason, int $userId): void` |
| **Returns** | `void` |
| **Throws** | `\Exception` |
| **Example** | `$inv->adjust(1, 500, 'Annual count', $adminId);` |
| **Side effects** | Updates `Inventory.quantity_on_hand`, creates `InventoryMovement(type: adjustment)` |

### Method: receiveReturn
| Property | Value |
|----------|-------|
| **Signature** | `receiveReturn(ReturnItem $returnItem): void` |
| **Returns** | `void` |
| **Throws** | `\Exception` |
| **Example** | `$inv->receiveReturn($returnItem);` |
| **Side effects** | Creates `InventoryMovement(type: return_good|return_damaged)`, updates `BatchItemStock.quantity` |

### Method: receiveGoods
| Property | Value |
|----------|-------|
| **Signature** | `receiveGoods(int $itemId, int $itemPackId, int $warehouseId, float $quantity, ?int $batchItemId = null, ?int $userId = null): void` |
| **Returns** | `void` |
| **Throws** | None |
| **Side effects** | Creates `Inventory` if not exists, creates `InventoryMovement(type: in)`, updates `BatchItemStock.quantity` |

### Method: reverseCommit
| Property | Value |
|----------|-------|
| **Signature** | `reverseCommit(Invoice $invoice): void` |
| **Returns** | `void` |
| **Throws** | None |
| **Side effects** | Restores `BatchItemStock.quantity`, creates `InventoryMovement(type: adjustment)` |

### Method: getAvailable
| Property | Value |
|----------|-------|
| **Signature** | `getAvailable(int $itemId, int $itemPackId, int $warehouseId): float` |
| **Returns** | `float` |
| **Throws** | None |
| **Side effects** | None |

### Method: getAvailability
| Property | Value |
|----------|-------|
| **Signature** | `getAvailability(int $itemId, int $itemPackId, int $warehouseId): AvailabilityResult` |
| **Returns** | `AvailabilityResult` DTO |
| **Throws** | None |
| **Side effects** | None |

### Method: allocateFefo
| Property | Value |
|----------|-------|
| **Signature** | `allocateFefo(int $itemId, int $itemPackId, int $warehouseId, float $quantity): Collection` |
| **Returns** | `Collection<int, array>` of FefoAllocation data |
| **Throws** | `InsufficientStockException` |
| **Side effects** | None (read-only query) |

---

## 3. WalletService

**Interface:** `App\Contracts\WalletServiceInterface`

### Method: deposit
| Property | Value |
|----------|-------|
| **Signature** | `deposit(Customer $customer, float $amount, ?string $method = null, ?string $referenceNumber = null, ?int $userId = null, ?string $notes = null): WalletTransaction` |
| **Returns** | `WalletTransaction` |
| **Throws** | `\Exception` if wallet not enabled |
| **Example** | `$wallet->deposit($customer, 1000, 'bank_transfer', 'TRX123', $adminId);` |
| **Side effects** | Creates `WalletTransaction`, fires `WalletDeposited` event, AuditLog |

### Method: debit
| Property | Value |
|----------|-------|
| **Signature** | `debit(Customer $customer, float $amount, Invoice $invoice, ?string $notes = null, ?int $userId = null): WalletTransaction` |
| **Returns** | `WalletTransaction` |
| **Throws** | `InsufficientBalanceException` (unless credit customer) |
| **Example** | `$wallet->debit($customer, 500, $invoice);` |
| **Side effects** | Creates `WalletTransaction`, fires `WalletDebited` event, AuditLog |

### Method: refund
| Property | Value |
|----------|-------|
| **Signature** | `refund(Customer $customer, float $amount, CreditNote $creditNote, ?int $userId = null, ?string $notes = null): WalletTransaction` |
| **Returns** | `WalletTransaction` |
| **Throws** | None |
| **Example** | `$wallet->refund($customer, 100, $creditNote);` |
| **Side effects** | Creates `WalletTransaction`, AuditLog |

### Method: adjust
| Property | Value |
|----------|-------|
| **Signature** | `adjust(Customer $customer, float $newBalance, string $reason, int $userId): WalletTransaction` |
| **Returns** | `WalletTransaction` |
| **Throws** | `\Exception` if wallet not active |
| **Example** | `$wallet->adjust($customer, 1500, 'Manual correction', $adminId);` |
| **Side effects** | Updates `CustomerWallet.balance`, creates `WalletTransaction`, AuditLog |

### Method: getBalance
| Property | Value |
|----------|-------|
| **Signature** | `getBalance(Customer $customer, int $currencyId): float` |
| **Returns** | `float` |
| **Throws** | None |
| **Side effects** | None |

### Method: getOrCreateWallet (internal)
| Property | Value |
|----------|-------|
| **Signature** | `getOrCreateWallet(Customer $customer): CustomerWallet` |
| **Returns** | `CustomerWallet` |
| **Throws** | `\Exception` if wallet not enabled |
| **Side effects** | May create wallet |

### Method: hasSufficientBalance
| Property | Value |
|----------|-------|
| **Signature** | `hasSufficientBalance(Customer $customer, float $amount, int $currencyId): bool` |
| **Returns** | `bool` |
| **Throws** | None |
| **Side effects** | None |

---

## 4. OrderLifecycleService

**Interface:** `App\Contracts\OrderLifecycleServiceInterface`

### Method: transition
| Property | Value |
|----------|-------|
| **Signature** | `transition(Order $order, OrderStatus $newStatus, ?User $user = null, ?string $notes = null, bool $fromSystem = false): Order` |
| **Returns** | `Order` |
| **Throws** | `InvalidTransitionException` |
| **Example** | `$ol->transition($order, OrderStatus::Approved, $admin, 'Approved');` |
| **Side effects** | Updates `Order.status`, creates `OrderStatusHistory`, fires `OrderStatusChanged` event |

### Method: canTransitionTo
| Property | Value |
|----------|-------|
| **Signature** | `canTransitionTo(Order $order, OrderStatus $newStatus): bool` |
| **Returns** | `bool` |
| **Throws** | None |
| **Side effects** | None |

### Method: getAllowedTransitions
| Property | Value |
|----------|-------|
| **Signature** | `getAllowedTransitions(Order $order): array` |
| **Returns** | `array<OrderStatus>` |
| **Throws** | None |
| **Side effects** | None |

### Method: markAsInvoiceGenerated
| Property | Value |
|----------|-------|
| **Signature** | `markAsInvoiceGenerated(Order $order, ?User $user = null): Order` |
| **Returns** | `Order` |
| **Throws** | None |
| **Side effects** | Auto-transitions to `invoice_generated` if allowed |

### Method: markAsInDelivery
| Property | Value |
|----------|-------|
| **Signature** | `markAsInDelivery(Order $order, ?User $user = null): Order` |
| **Returns** | `Order` |
| **Throws** | None |
| **Side effects** | Auto-transitions to `in_delivery` if allowed |

---

## 5. InvoiceLifecycleService

**Interface:** `App\Contracts\InvoiceLifecycleServiceInterface`

### Method: approve
| Property | Value |
|----------|-------|
| **Signature** | `approve(Invoice $invoice, ?User $user = null, ?string $notes = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `InvalidTransitionException` |
| **Example** | `$il->approve($invoice, $admin);` |
| **Side effects** | Sets `status=approved`, `approved_at`, `approved_by`, payment_deadline or due_date, fires `InvoiceApproved` event |

### Method: applyDeadline (internal)
| Property | Value |
|----------|-------|
| **Signature** | `applyDeadline(Invoice $invoice): void` |
| **Returns** | `void` |
| **Throws** | None |
| **Side effects** | Sets `payment_deadline_at` based on settings |

### Method: extendDeadline
| Property | Value |
|----------|-------|
| **Signature** | `extendDeadline(Invoice $invoice, int $additionalHours, User $user, ?string $notes = null): Carbon` |
| **Returns** | `Carbon` (new deadline) |
| **Throws** | None |
| **Example** | `$il->extendDeadline($invoice, 24, $admin, 'Extended');` |
| **Side effects** | Updates `payment_deadline_at`, AuditLog |

### Method: markAsPaid
| Property | Value |
|----------|-------|
| **Signature** | `markAsPaid(Invoice $invoice, ?User $user = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `InvalidTransitionException` |
| **Side effects** | Sets `status=paid`, `paid_amount=total`, `paid_at` |

### Method: markAsPartiallyPaid
| Property | Value |
|----------|-------|
| **Signature** | `markAsPartiallyPaid(Invoice $invoice, float $amount, ?User $user = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `InvalidTransitionException` |
| **Side effects** | Updates `paid_amount`, sets `status=paid` or `partially_paid` |

### Method: reject
| Property | Value |
|----------|-------|
| **Signature** | `reject(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `InvalidTransitionException` |
| **Side effects** | Sets `status=rejected`, AuditLog |

### Method: cancel
| Property | Value |
|----------|-------|
| **Signature** | `cancel(Invoice $invoice, ?User $user = null, ?string $reason = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `InvalidTransitionException` |
| **Side effects** | Sets `status=cancelled`, reverses stock commitment, AuditLog |

### Method: expire
| Property | Value |
|----------|-------|
| **Signature** | `expire(Invoice $invoice): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | None |
| **Side effects** | Sets `status=expired`, releases reservations, reverses stock, fires `InvoiceExpired` event |

### Method: canTransitionTo
| Property | Value |
|----------|-------|
| **Signature** | `canTransitionTo(Invoice $invoice, InvoiceStatus $status): bool` |
| **Returns** | `bool` |
| **Throws** | None |
| **Side effects** | None |

### Method: getAllowedTransitions
| Property | Value |
|----------|-------|
| **Signature** | `getAllowedTransitions(Invoice $invoice): array` |
| **Returns** | `array<InvoiceStatus>` |
| **Throws** | None |
| **Side effects** | None |

---

## 6. DeliveryService

**Interface:** `App\Contracts\DeliveryServiceInterface`

### Method: assignDriver
| Property | Value |
|----------|-------|
| **Signature** | `assignDriver(Invoice $invoice, ?Vehicle $vehicle = null, ?User $driver = null): Delivery` |
| **Returns** | `Delivery` |
| **Throws** | None |
| **Example** | `$ds->assignDriver($invoice, $vehicle, $driver);` |
| **Side effects** | Creates `Delivery`, creates `DeliveryStageLog(stage: shipping)` |

### Method: advanceStage
| Property | Value |
|----------|-------|
| **Signature** | `advanceStage(Delivery $delivery, string $stage, ?User $user = null, ?float $latitude = null, ?float $longitude = null, ?string $notes = null, ?string $photoPath = null): Delivery` |
| **Returns** | `Delivery` |
| **Throws** | None |
| **Example** | `$ds->advanceStage($delivery, 'out_for_delivery', $driver, 32.5, 13.6);` |
| **Side effects** | Updates `Delivery.current_stage`, creates `DeliveryStageLog`, fires `DeliveryStageChanged` event |

### Method: getTimeline
| Property | Value |
|----------|-------|
| **Signature** | `getTimeline(Invoice|Order $model): array` |
| **Returns** | `array` of stage logs with timestamp, GPS, notes |
| **Throws** | None |
| **Side effects** | None |

### Method: cancel
| Property | Value |
|----------|-------|
| **Signature** | `cancel(Invoice $invoice, ?string $reason = null): ?Delivery` |
| **Returns** | `?Delivery` |
| **Throws** | None |
| **Side effects** | Deletes delivery record |

---

## 7. ReturnService

**Interface:** `App\Contracts\ReturnServiceInterface`

### Method: createRequest
| Property | Value |
|----------|-------|
| **Signature** | `createRequest(Invoice $invoice, Customer $customer, string $type, string $reason, array $items, ?User $user = null): ReturnRequest` |
| **Returns** | `ReturnRequest` |
| **Throws** | `\Exception` |
| **Example** | `$rs->createRequest($invoice, $customer, 'partial', 'Damaged', [...]);` |
| **Side effects** | Creates `ReturnRequest`, `ReturnItem[]`, fires `ReturnRequested` event, AuditLog |

### Method: approve
| Property | Value |
|----------|-------|
| **Signature** | `approve(ReturnRequest $request, ?User $reviewer = null, ?string $notes = null): ReturnRequest` |
| **Returns** | `ReturnRequest` |
| **Throws** | `\Exception` if not in `pending` status |
| **Side effects** | Sets `status=approved`, `reviewed_by`, `reviewed_at`, AuditLog |

### Method: reject
| Property | Value |
|----------|-------|
| **Signature** | `reject(ReturnRequest $request, ?User $reviewer = null, ?string $reason = null): ReturnRequest` |
| **Returns** | `ReturnRequest` |
| **Throws** | `\Exception` if not in `pending` status |
| **Side effects** | Sets `status=rejected`, AuditLog |

### Method: process
| Property | Value |
|----------|-------|
| **Signature** | `process(ReturnRequest $request): CreditNote` |
| **Returns** | `CreditNote` |
| **Throws** | `\Exception` |
| **Example** | `$rs->process($request);` |
| **Side effects** | Creates `CreditNote`, returns inventory, credits wallet, fires `ReturnProcessed` event, AuditLog |

### Method: applyToInvoice
| Property | Value |
|----------|-------|
| **Signature** | `applyToInvoice(CreditNote $creditNote, ?Invoice $invoice = null): Invoice` |
| **Returns** | `Invoice` |
| **Throws** | `\Exception` |
| **Side effects** | Reduces invoice balance, creates AuditLog |

---

## 8. PaymentDeadlineService

**Interface:** `App\Contracts\PaymentDeadlineServiceInterface`

### Method: applyDeadline
| Property | Value |
|----------|-------|
| **Signature** | `applyDeadline(Invoice $invoice): ?Carbon` |
| **Returns** | `?Carbon` (new deadline) |
| **Throws** | None |
| **Example** | `$service->applyDeadline($invoice);` |
| **Side effects** | Sets `payment_deadline_at` based on settings |

### Method: extend
| Property | Value |
|----------|-------|
| **Signature** | `extend(Invoice $invoice, int $hours, User $user, ?string $notes = null): void` |
| **Returns** | `void` |
| **Throws** | None |
| **Example** | `$service->extend($invoice, 24, $admin, 'Customer requested extension');` |
| **Side effects** | Updates `payment_deadline_at`, AuditLog |

### Method: expireUnpaid
| Property | Value |
|----------|-------|
| **Signature** | `expireUnpaid(): array` |
| **Returns** | `array<Invoice>` (expired invoices) |
| **Throws** | None |
| **Example** | `$service->expireUnpaid();` |
| **Side effects** | Expires invoices, releases reservations, reverses inventory, fires `InvoiceExpired` event |

---

*Generated: 2026-09-17*
