<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\PriceResult;
use App\DTOs\BulkQuoteResult;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemPack;
use Carbon\Carbon;

interface PricingServiceInterface
{
    /**
     * Calculate price for a single item given domain objects.
     * This is the primary method used internally by OrderService and QuotationService.
     *
     * @param Item $item The item to price
     * @param Customer $customer The customer (determines price list, tax group)
     * @param float $quantity Quantity to price for
     * @param ItemPack|null $itemPack Optional pack (affects unit of measure)
     * @param Carbon|null $date Optional date for time-sensitive prices (valid from/to)
     * @return PriceResult Full pricing result including tax and discount
     */
    public function calculatePrice(
        Item $item,
        Customer $customer,
        float $quantity = 1.0,
        ?ItemPack $itemPack = null,
        ?Carbon $date = null,
    ): PriceResult;

    /**
     * Retrieve price using scalar IDs — delegates internally to calculatePrice().
     *
     * @param Customer $customer The customer
     * @param int $itemId The item ID
     * @param int $itemPackId The item pack ID
     * @param float $quantity Quantity to price for
     * @param Carbon|null $date Optional date for time-sensitive prices
     * @param int|null $currencyId Optional currency ID for exchange rate
     * @return PriceResult
     */
    public function getPrice(
        Customer $customer,
        int $itemId,
        int $itemPackId,
        float $quantity,
        ?Carbon $date = null,
        ?int $currencyId = null,
    ): PriceResult;

    /**
     * Quote multiple items in a single operation.
     *
     * @param Customer $customer The customer to price for
     * @param array $items Array of item definitions with keys: item_id, item_pack_id, quantity
     * @return BulkQuoteResult Aggregated result with all lines and totals
     */
    public function bulkQuote(Customer $customer, array $items): BulkQuoteResult;

    /**
     * Resolve customer price list entry for an item+pack combination.
     *
     * @param Customer $customer
     * @param Item $item
     * @param ItemPack $pack
     * @param float $quantity
     * @param Carbon $date
     * @return array{unit_price: float, discount_percent: float, price_list_id: int}|null
     */
    public function resolveCustomerPrice(
        Customer $customer,
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array;

    /**
     * Resolve default price list entry for an item+pack combination.
     *
     * @param Item $item
     * @param ItemPack $pack
     * @param float $quantity
     * @param Carbon $date
     * @return array{unit_price: float, discount_percent: float, price_list_id: int}|null
     */
    public function resolveDefaultPriceListPrice(
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array;
}
