# Domain Exceptions

## Base Exception

All custom domain exceptions extend `App\Exceptions\DomainException`, which provides:

- A consistent `render()` method that returns a JSON response with `message`, `code`, and optional `errors` fields.
- Support for a `statusCode` property and `context` array for additional error details.

**Usage:**
```php
throw new InsufficientBalanceException(
    required: 500.00,
    available: 200.00,
    currencyCode: 'SAR',
);
```

## Exception Catalog

| Exception | File | HTTP Status | Thrown By | Description |
|-----------|------|-------------|-----------|-------------|
| `InsufficientBalanceException` | `app/Exceptions/InsufficientBalanceException.php` | 422 | `WalletService::debit()` | Customer wallet has insufficient funds for a debit operation (non-credit customers only). |
| `CreditLimitExceededException` | `app/Exceptions/CreditLimitExceededException.php` | 422 | `WalletService::debit()` | Credit customer's wallet + credit_limit combination is insufficient to cover the requested amount. |
| `InsufficientStockException` | `app/Exceptions/InsufficientStockException.php` | 422 | `InventoryService::reserve()`, `OrderService::create()` | Requested quantity exceeds available stock at a given warehouse. |
| `ItemNotFoundException` | `app/Exceptions/ItemNotFoundException.php` | 404 | `OrderService::create()`, `OrderService::addItem()` | The specified Item (or ItemPack) could not be found. |
| `NoPriceAvailableException` | `app/Exceptions/NoPriceAvailableException.php` | 404 | `OrderService::create()`, `OrderService::addItem()` | No price could be resolved for the item with the customer's price list. |
| `InvalidTransitionException` | `app/Exceptions/InvalidTransitionException.php` | 422 | `OrderLifecycleService::transition()` | An attempt was made to move an entity to a status that is not in its allowed transitions. |
| `ReservationExpiredException` | `app/Exceptions/ReservationExpiredException.php` | 422 | `InventoryService::release()` | A stock reservation has passed its expiry time and can no longer be consumed. |

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

## Error Response Format

All exceptions render as JSON in the following format:

```json
{
    "message": "Insufficient wallet balance: required 500.00 SAR, available 200.00 SAR",
    "code": 422,
    "errors": null
}
```

If the `context` property is set on the exception, it will be included in the `errors` field:

```json
{
    "message": "Insufficient stock: requested 100, available 50 for item 42 at warehouse 3",
    "code": 422,
    "errors": {
        "requested": 100,
        "available": 50,
        "item_id": 42,
        "warehouse_id": 3
    }
}
```
