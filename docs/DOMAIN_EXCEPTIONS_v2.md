# Domain Exceptions v2
*Last Updated: 2026-09-17*

## Base Exception

All custom domain exceptions extend `App\Exceptions\DomainException`, which provides:
- A `render()` method returning a JSON response with `message`, `code`, and `errors` fields.
- Support for `statusCode` and `context` properties.

## Exception Catalog

| # | Exception | File | HTTP Status | Thrown By | Description |
|---|---|---|---|---|---|
| 1 | `DomainException` (base) | `app/Exceptions/DomainException.php` | 422 (default) | All custom exceptions extend this | Base class with consistent JSON rendering |
| 2 | `InsufficientBalanceException` | `app/Exceptions/InsufficientBalanceException.php` | 422 | `WalletService::debit()` | Wallet balance insufficient (non-credit customers) |
| 3 | `CreditLimitExceededException` | `app/Exceptions/CreditLimitExceededException.php` | 422 | `WalletService::debit()` | Credit customer's wallet + credit limit insufficient |
| 4 | `InsufficientStockException` | `app/Exceptions/InsufficientStockException.php` | 422 | `InventoryService::reserve()`, `OrderService::create()` | Requested quantity exceeds available stock |
| 5 | `ItemNotFoundException` | `app/Exceptions/ItemNotFoundException.php` | 404 | `OrderService::create()`, `OrderService::addItem()` | Item or ItemPack not found |
| 6 | `NoPriceAvailableException` | `app/Exceptions/NoPriceAvailableException.php` | 404 | `OrderService::create()`, `OrderService::addItem()` | No price found for customer's price list |
| 7 | `InvalidTransitionException` | `app/Exceptions/InvalidTransitionException.php` | 422 | `OrderLifecycleService::transition()`, `InvoiceLifecycleService::approve()` | Invalid status transition |
| 8 | `ReservationExpiredException` | `app/Exceptions/ReservationExpiredException.php` | 422 | `InventoryService::release()` | Stock reservation has expired |
| 9 | `WalletNotEnabledException` | `app/Exceptions/WalletNotEnabledException.php` | 400 | `WalletService::getOrCreateWallet()` | Customer has wallet disabled or no wallet exists |
| 10 | `TaxCalculationException` | `app/Exceptions/TaxCalculationException.php` | 422 | `PricingService::calculatePrice()` | Tax calculation failed (invalid tax class, customer tax group, etc.) |
| 11 | `InvalidQuotationStateException` | `app/Exceptions/InvalidQuotationStateException.php` | 422 | `QuotationService::acceptQuote()`, `QuotationService::rejectQuote()` | Quotation is in a state that doesn't allow the operation |
| 12 | `InvalidReturnStateException` | `app/Exceptions/InvalidReturnStateException.php` | 422 | `ReturnService::processReturn()`, `ReturnService::validateReturn()` | Return cannot be processed due to invalid invoice/order state |
| 13 | `DeliveryStageSkipException` | `app/Exceptions/DeliveryStageSkipException.php` | 422 | `DeliveryService::assignDriver()`, `DeliveryService::updateStatus()` | Attempted to skip a required delivery stage (e.g., jump from pending to delivered) |
| 14 | `PaymentRecordingException` | `app/Exceptions/PaymentRecordingException.php` | 422 | `PaymentService::recordPayment()` | Payment could not be recorded (validation failure, reconciliation mismatch, etc.) |
| 15 | `PriceListNotFoundException` | `app/Exceptions/PriceListNotFoundException.php` | 404 | `PricingService::calculatePrice()` | No price list available for the customer |

## Exception Details

### InsufficientBalanceException
```php
new InsufficientBalanceException(
    required: float,
    available: float,
    currencyCode: string,
)
```

### CreditLimitExceededException
```php
new CreditLimitExceededException(
    required: float,
    limit: float,
    currentBalance: float,
)
```

### InsufficientStockException
```php
new InsufficientStockException(
    requested: float,
    available: float,
    itemId: int|null = null,
    itemPackId: int|null = null,
    warehouseId: int|null = null,
)
```

### ItemNotFoundException
```php
new ItemNotFoundException(
    itemId: int,
    itemPackId: int|null = null,
)
```

### NoPriceAvailableException
```php
new NoPriceAvailableException(
    itemId: int,
    itemPackId: int|null = null,
)
```

### InvalidTransitionException
```php
new InvalidTransitionException(
    entityType: string,
    fromStatus: string,
    toStatus: string,
    entityId: int|null = null,
)
```

### ReservationExpiredException
```php
new ReservationExpiredException(
    reservationId: int,
)
```

### WalletNotEnabledException
```php
new WalletNotEnabledException(
    customerId: int,
)
```

### TaxCalculationException
```php
new TaxCalculationException(
    reason: string,
    itemId: int|null = null,
    customerId: int|null = null,
)
```

### InvalidQuotationStateException
```php
new InvalidQuotationStateException(
    quoteId: int,
    currentState: string,
    attemptedAction: string,
)
```

### InvalidReturnStateException
```php
new InvalidReturnStateException(
    orderId: int,
    reason: string,
)
```

### DeliveryStageSkipException
```php
new DeliveryStageSkipException(
    deliveryId: int,
    currentStage: string,
    targetStage: string,
)
```

### PaymentRecordingException
```php
new PaymentRecordingException(
    reason: string,
    invoiceId: int|null = null,
)
```

### PriceListNotFoundException
```php
new PriceListNotFoundException(
    customerId: int,
)
```
