<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PricingServiceInterface;
use App\DTOs\PriceResult;
use App\DTOs\BulkQuoteResult;
use App\DTOs\QuotedLine;
use App\Enums\PriceSource;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\NoPriceAvailableException;
use App\Models\Customer;
use App\Models\CustomerPriceList;
use App\Models\Currency;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceList;
use App\Models\PriceListItem;
use Carbon\Carbon;

final class PricingService implements PricingServiceInterface
{
    public function calculatePrice(
        Item $item,
        Customer $customer,
        float $quantity = 1.0,
        ?ItemPack $itemPack = null,
        ?Carbon $date = null,
    ): PriceResult {
        $date ??= now();
        $pack = $itemPack ?? $this->getDefaultPack($item);

        $priceData = $this->resolveCustomerPrice($customer, $item, $pack, $quantity, $date);
        $source = PriceSource::CustomerPriceList;
        $priceListItemId = null;

        if ($priceData === null) {
            $priceData = $this->resolveDefaultPriceListPrice($item, $pack, $quantity, $date);
            $source = PriceSource::DefaultPriceList;
        }

        if ($priceData === null) {
            $unitPrice = (float) $item->price;
            if ($unitPrice <= 0) {
                throw new NoPriceAvailableException(itemId: $item->id);
            }
            $discountPercent = 0.0;
            $source = PriceSource::Base;
        } else {
            $unitPrice = (float) $priceData['unit_price'];
            $discountPercent = (float) ($priceData['discount_percent'] ?? 0);
            $priceListItemId = $priceData['price_list_item_id'] ?? null;
        }

        $discount = $unitPrice * $discountPercent / 100;
        $finalUnitPrice = $unitPrice - $discount;

        [$taxPercent, $taxAmount] = $this->calculateTax($finalUnitPrice, $customer, $item);

        $currencyId = $this->resolveCurrencyId($customer);
        $exchangeRate = 1.0;

        return new PriceResult(
            unitPrice: $unitPrice,
            discount: $discount,
            finalUnitPrice: $finalUnitPrice,
            taxPercent: $taxPercent,
            taxAmount: $taxAmount,
            source: $source,
            priceListItemId: $priceListItemId,
            currencyId: $currencyId,
            exchangeRate: $exchangeRate,
        );
    }

    public function getPrice(
        Customer $customer,
        int $itemId,
        int $itemPackId,
        float $quantity,
        ?Carbon $date = null,
        ?int $currencyId = null,
    ): PriceResult {
        $item = Item::find($itemId);
        if (!$item) {
            throw new ItemNotFoundException(itemId: $itemId);
        }

        $pack = ItemPack::find($itemPackId);
        if (!$pack) {
            throw new ItemNotFoundException(itemId: $itemId, itemPackId: $itemPackId);
        }

        return $this->calculatePrice($item, $customer, $quantity, $pack, $date);
    }

    public function bulkQuote(Customer $customer, array $items): BulkQuoteResult
    {
        $lines = [];
        $total = 0.0;
        $currencyId = null;

        foreach ($items as $itemData) {
            $priceResult = $this->getPrice(
                customer: $customer,
                itemId: (int) $itemData['item_id'],
                itemPackId: (int) $itemData['item_pack_id'],
                quantity: (float) ($itemData['quantity'] ?? 1.0),
                date: isset($itemData['date']) ? Carbon::parse($itemData['date']) : null,
                currencyId: isset($itemData['currency_id']) ? (int) $itemData['currency_id'] : null,
            );

            if ($currencyId === null) {
                $currencyId = $priceResult->currencyId;
            } elseif ($currencyId !== $priceResult->currencyId) {
                throw new \InvalidArgumentException("All items in a bulk quote must use the same currency.");
            }

            $qty = (float) ($itemData['quantity'] ?? 1.0);
            $lineSubtotal = $priceResult->finalUnitPrice * $qty;
            $lineTaxAmount = $priceResult->taxAmount * $qty;
            $lineTotal = $lineSubtotal + $lineTaxAmount;

            $quotedLine = new QuotedLine(
                itemId: (int) $itemData['item_id'],
                itemPackId: $itemData['item_pack_id'] ?? null,
                quantity: $qty,
                unitPrice: $priceResult->unitPrice,
                discount: $priceResult->discount,
                finalUnitPrice: $priceResult->finalUnitPrice,
                taxPercent: $priceResult->taxPercent,
                taxAmount: $lineTaxAmount,
                subtotal: $lineSubtotal,
                total: $lineTotal,
                priceListItemId: $priceResult->priceListItemId,
                source: $priceResult->source,
                currencyId: $priceResult->currencyId,
                exchangeRate: $priceResult->exchangeRate,
            );

            $lines[] = $quotedLine;
            $total += $lineTotal;
        }

        return new BulkQuoteResult(
            lines: collect($lines),
            totalLines: count($lines),
            totalAmount: $total,
            currencyId: $currencyId ?? 1,
        );
    }

    public function resolveDefaultPriceListPrice(
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array {
        $priceList = PriceList::where('status', 'active')->first();

        if (!$priceList) {
            return null;
        }

        // Get the active version to find the effective price list
        $activeVersion = $priceList->versions()
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $date)
            ->latest('effective_date')
            ->first();

        // Use the price list's items (PriceListItem has tiered pricing fields)
        $priceItem = $this->findBestPriceItem($priceList->id, $item, $pack, $quantity, $date);

        if (!$priceItem) {
            return null;
        }

        return [
            'unit_price' => (float) $priceItem->price,
            'discount_percent' => (float) ($priceItem->discount_percent ?? 0),
            'price_list_item_id' => $priceItem->id,
        ];
    }

    public function resolveCustomerPrice(
        Customer $customer,
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?array {
        $customerPriceList = CustomerPriceList::where('customer_id', $customer->id)
            ->where('is_active', true)
            ->orderByDesc('assigned_at')
            ->first();

        if (!$customerPriceList) {
            return null;
        }

        $priceList = $customerPriceList->priceList;
        if (!$priceList) {
            return null;
        }

        // Get the active version
        $activeVersion = $priceList->versions()
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $date)
            ->latest('effective_date')
            ->first();

        if (!$activeVersion) {
            return null;
        }

        // Use the price list's items (PriceListItem has tiered pricing fields)
        $priceItem = $this->findBestPriceItem($priceList->id, $item, $pack, $quantity, $date);

        if (!$priceItem) {
            return null;
        }

        return [
            'unit_price' => (float) $priceItem->price,
            'discount_percent' => (float) ($priceItem->discount_percent ?? 0),
            'price_list_item_id' => $priceItem->id,
        ];
    }

    /**
     * Find the best matching price list item using tiered pricing.
     *
     * @param int $priceListId
     * @param Item $item
     * @param ItemPack $pack
     * @param float $quantity
     * @param Carbon $date
     * @return PriceListItem|null
     */
    private function findBestPriceItem(
        int $priceListId,
        Item $item,
        ItemPack $pack,
        float $quantity,
        Carbon $date,
    ): ?PriceListItem {
        $baseQuery = PriceListItem::where('price_list_id', $priceListId)
            ->where('item_id', $item->id)
            ->where('item_pack_id', $pack->id)
            ->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            });

        $priceItem = (clone $baseQuery)
            ->where('min_quantity', '<=', $quantity)
            ->orderByDesc('min_quantity')
            ->first();

        return $priceItem ?? (clone $baseQuery)
            ->where(function ($q) {
                $q->whereNull('min_quantity')->orWhere('min_quantity', '<=', 1);
            })
            ->orderBy('min_quantity')
            ->first();
    }

    /**
     * Get the default pack for an item (first pack by sort_order).
     *
     * @param Item $item
     * @return ItemPack
     */
    private function getDefaultPack(Item $item): ItemPack
    {
        return ItemPack::where('item_id', $item->id)
            ->orderBy('sort_order', 'asc')
            ->first()
            ?? throw new ItemNotFoundException(itemId: $item->id);
    }

    /**
     * Resolve the currency ID for a customer.
     * Falls back to base currency (Currency.base_currency = true).
     *
     * @param Customer $customer
     * @return int
     */
    private function resolveCurrencyId(Customer $customer): int
    {
        return Currency::where('base_currency', true)->value('id') ?? 1;
    }

    /**
     * Placeholder: Pending TaxService v2 — calculate tax based on customer tax group and item tax class.
     * Currently returns 0 tax.
     *
     * @param float $amount
     * @param Customer $customer
     * @param Item $item
     * @return array{0: float, 1: float}
     */
    private function calculateTax(float $amount, Customer $customer, Item $item): array
    {
        return [0.0, 0.0];
    }
}
