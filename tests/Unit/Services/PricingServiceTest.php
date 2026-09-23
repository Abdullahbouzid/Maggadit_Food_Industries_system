<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\PricingServiceInterface;
use App\DTOs\PriceResult;
use App\DTOs\QuotedLine;
use App\DTOs\BulkQuoteResult;
use App\Enums\PriceSource;
use App\Exceptions\ExchangeRateNotFoundException;
use App\Exceptions\ItemNotFoundException;
use App\Exceptions\NoPriceAvailableException;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerPriceList;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\PriceListVersion;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingServiceInterface $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    // ===============================
    // Helper Methods
    // ===============================

    private function createBaseCurrency(): Currency
    {
        return Currency::query()->forceCreate([
            'name' => 'LIBYAN DINAR',
            'code' => 'LYD',
            'coin_description' => 'Dinar',
            'base_currency' => true,
            'exchange_rate_to_base' => 1.0,
        ]);
    }

    private function createForeignCurrency(float $rateToBase): Currency
    {
        return Currency::query()->forceCreate([
            'name' => 'US DOLLAR',
            'code' => 'USD',
            'coin_description' => 'Dollar',
            'base_currency' => false,
            'exchange_rate_to_base' => $rateToBase,
        ]);
    }

    private function createCustomer(string $suffix = ''): Customer
    {
        return Customer::create([
            'name' => 'Test Customer ' . $suffix,
            'username' => 'testuser' . $suffix,
            'email' => 'test' . $suffix . '@example.com',
            'password' => bcrypt('password'),
            'price_list_id' => null,
            'customer_type' => 'cash',
            'credit_limit' => 0,
            'current_balance' => 0,
            'payment_terms_days' => 0,
            'wallet_enabled' => true,
        ]);
    }

    private function createItemWithPack(float $price = 100.00): array
    {
        $item = Item::create([
            'code' => 'TEST-' . uniqid(),
            'name_en' => 'Test Item',
            'name_ar' => 'منتج تجريبي',
            'is_inactive' => false,
        ]);

        $pack = ItemPack::create([
            'item_id' => $item->id,
            'uom_id' => 1,
            'quantity_in_pack' => 1,
            'barcode' => '1234567890123',
            'is_default_pack' => true,
            'sort_order' => 0,
            'price' => $price,
        ]);

        return [$item, $pack];
    }

    // ===============================
    // Happy Path Tests (5)
    // ===============================

    public function test_returns_customer_price_list_price_when_available(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('a');
        [$item, $pack] = $this->createItemWithPack(100.0);

        $priceList = PriceList::create([
            'name' => 'Customer Price List',
            'status' => 'active',
            'is_default' => false,
        ]);

        CustomerPriceList::create([
            'customer_id' => $customer->id,
            'price_list_id' => $priceList->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 80.00,
            'min_quantity' => 1,
            'discount_percent' => 0,
            'is_active' => true,
        ]);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer, 10, $pack);

        /// Assert
        $this->assertEquals(80.00, $result->unitPrice);
        $this->assertEquals(PriceSource::CustomerPriceList, $result->source);
        $this->assertNotNull($result->priceListItemId);
    }

    public function test_returns_default_price_list_price_when_no_customer_list(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('b');
        [$item, $pack] = $this->createItemWithPack(100.0);

        $priceList = PriceList::create([
            'name' => 'Default Price List',
            'status' => 'active',
            'is_default' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 75.00,
            'min_quantity' => 1,
            'discount_percent' => 0,
            'is_active' => true,
        ]);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer);

        /// Assert
        $this->assertEquals(75.00, $result->unitPrice);
        $this->assertEquals(PriceSource::DefaultPriceList, $result->source);
    }

    public function test_returns_base_item_price_when_no_price_lists(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('c');
        [$item, $pack] = $this->createItemWithPack(100.0);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer);

        /// Assert
        $this->assertEquals(100.00, $result->unitPrice);
        $this->assertEquals(PriceSource::Base, $result->source);
        $this->assertNull($result->priceListItemId);
    }

    public function test_applies_discount_from_price_list_item(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('d');
        [$item, $pack] = $this->createItemWithPack(100.0);

        $priceList = PriceList::create([
            'name' => 'Customer Price List',
            'status' => 'active',
        ]);

        CustomerPriceList::create([
            'customer_id' => $customer->id,
            'price_list_id' => $priceList->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 100.00,
            'min_quantity' => 1,
            'discount_percent' => 20.00,
            'is_active' => true,
        ]);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer, 5, $pack);

        /// Assert
        $this->assertEquals(100.00, $result->unitPrice);
        $this->assertEquals(20.00, $result->discount);
        $this->assertEquals(80.00, $result->finalUnitPrice);
    }

    public function test_uses_tiered_pricing_with_highest_min_quantity_below_qty(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('e');
        [$item, $pack] = $this->createItemWithPack(100.0);

        $priceList = PriceList::create([
            'name' => 'Tiered Price List',
            'status' => 'active',
        ]);

        CustomerPriceList::create([
            'customer_id' => $customer->id,
            'price_list_id' => $priceList->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 100.00,
            'min_quantity' => 1,
            'discount_percent' => 0,
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 90.00,
            'min_quantity' => 10,
            'discount_percent' => 10,
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 80.00,
            'min_quantity' => 50,
            'discount_percent' => 20,
            'is_active' => true,
        ]);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer, 25, $pack);

        /// Assert
        $this->assertEquals(90.00, $result->unitPrice);
        $this->assertEquals(10.00, $result->discount);
        $this->assertEquals(80.00, $result->finalUnitPrice);
    }

    // ===============================
    // Multi-Currency Tests (3)
    // ===============================

    public function test_returns_exchange_rate_1_for_same_currency(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('cur1');
        [$item, $pack] = $this->createItemWithPack(100.0);

        /// Act
        $result = $this->pricingService->calculatePrice($item, $customer);

        /// Assert
        $this->assertEquals(1.0, $result->exchangeRate);
    }

    public function test_converts_between_foreign_currencies_correctly(): void
    {
        /// Arrange
        $baseCurrency = $this->createBaseCurrency();
        $usdCurrency = $this->createForeignCurrency(0.21);

        $customer = $this->createCustomer('cur2');
        [$item, $pack] = $this->createItemWithPack(100.0);

        // 1 LYD (base) = 0.21 USD
        // Convert 100 LYD → USD: (1/1.0) * 0.21 = 0.21 → 100 * 0.21 = 21 USD
        $baseResult = $this->pricingService->calculatePrice($item, $customer, 1, $pack);
        $convertedResult = $this->pricingService->getPrice(
            $customer,
            $item->id,
            $pack->id,
            1,
            null,
            $usdCurrency->id,
        );

        /// Assert
        $this->assertEquals($baseCurrency->id, $baseResult->currencyId);
        $this->assertEquals(100.00, $baseResult->unitPrice);
        $this->assertEquals($usdCurrency->id, $convertedResult->currencyId);
        $this->assertEquals(0.21, round($convertedResult->exchangeRate, 6));
        // 100 LYD * 0.21 = 21 USD
        $this->assertEquals(21.00, round($convertedResult->unitPrice, 2));
    }

    public function test_getPrice_converts_to_requested_currency(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $usdCurrency = $this->createForeignCurrency(0.21);

        $customer = $this->createCustomer('cur3');
        [$item, $pack] = $this->createItemWithPack(100.0);

        /// Act
        $result = $this->pricingService->getPrice(
            $customer,
            $item->id,
            $pack->id,
            1,
            null,
            $usdCurrency->id,
        );

        /// Assert
        $this->assertEquals($usdCurrency->id, $result->currencyId);
        $this->assertEquals(21.00, round($result->unitPrice, 2));
    }

    // ===============================
    // BulkQuote Tests (3)
    // ===============================

    public function test_bulk_quote_aggregates_totals_correctly(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('bulk1');
        [$item1, $pack1] = $this->createItemWithPack(100.0);
        [$item2, $pack2] = $this->createItemWithPack(50.0);

        $items = [
            [
                'item_id' => $item1->id,
                'item_pack_id' => $pack1->id,
                'quantity' => 2,
            ],
            [
                'item_id' => $item2->id,
                'item_pack_id' => $pack2->id,
                'quantity' => 3,
            ],
        ];

        /// Act
        $result = $this->pricingService->bulkQuote($customer, $items);

        /// Assert
        $this->assertCount(2, $result->lines);
        $this->assertEquals(2, $result->totalLines);
        $this->assertEquals(350.00, $result->total); // 200 + 150
        $this->assertEquals(350.00, $result->subtotal);
        $this->assertEquals(0.0, $result->taxTotal);
    }

    public function test_bulk_quote_throws_when_currencies_differ(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('bulk2');
        [$item1, $pack1] = $this->createItemWithPack(100.0);
        [$item2, $pack2] = $this->createItemWithPack(50.0);

        $items = [
            [
                'item_id' => $item1->id,
                'item_pack_id' => $pack1->id,
                'quantity' => 1,
                'currency_id' => 1,
            ],
            [
                'item_id' => $item2->id,
                'item_pack_id' => $pack2->id,
                'quantity' => 1,
                'currency_id' => 2,
            ],
        ];

        /// Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->pricingService->bulkQuote($customer, $items);
    }

    public function test_bulk_quote_uses_cache_no_n_plus_1(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('bulk3');
        [$item, $pack] = $this->createItemWithPack(100.0);

        $priceList = PriceList::create([
            'name' => 'Customer Price List',
            'status' => 'active',
        ]);

        CustomerPriceList::create([
            'customer_id' => $customer->id,
            'price_list_id' => $priceList->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        PriceListItem::create([
            'price_list_id' => $priceList->id,
            'item_id' => $item->id,
            'item_pack_id' => $pack->id,
            'price' => 80.00,
            'min_quantity' => 1,
            'discount_percent' => 0,
            'is_active' => true,
        ]);

        PriceListVersion::create([
            'price_list_id' => $priceList->id,
            'effective_date' => now(),
            'is_active' => true,
        ]);

        $items = [];
        for ($i = 0; $i < 3; $i++) {
            $items[] = [
                'item_id' => $item->id,
                'item_pack_id' => $pack->id,
                'quantity' => 10,
            ];
        }

        /// Act
        DB::enableQueryLog();
        $this->pricingService->bulkQuote($customer, $items);
        $queries = DB::getQueryLog();

        /// Assert
        $versionQueries = array_filter($queries, fn ($q) =>
            str_contains($q['query'], 'price_list_versions')
        );
        $this->assertLessThanOrEqual(2, count($versionQueries));
    }

    // ===============================
    // Exception Tests (4)
    // ===============================

    public function test_throws_item_not_found_when_item_missing(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('exc1');

        /// Act & Assert
        $this->expectException(ItemNotFoundException::class);
        $this->pricingService->getPrice($customer, 99999, 99999, 1);
    }

    public function test_throws_no_price_available_when_all_sources_fail(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('exc2');
        $item = Item::create([
            'code' => 'FREE-' . uniqid(),
            'name_en' => 'Free Item',
            'name_ar' => 'منتج مجاني',
            'is_inactive' => false,
        ]);

        // Create item with no packs
        // Item::price accessor will return 0 since no packs exist

        /// Act & Assert
        $this->expectException(NoPriceAvailableException::class);
        $this->pricingService->calculatePrice($item, $customer);
    }

    public function test_throws_exchange_rate_not_found_when_currency_has_null_rate(): void
    {
        /// Arrange
        $baseCurrency = $this->createBaseCurrency();
        // Foreign currency without exchange_rate_to_base set
        $usdCurrency = Currency::query()->forceCreate([
            'name' => 'US DOLLAR',
            'code' => 'USD',
            'coin_description' => 'Dollar',
            'base_currency' => false,
            'exchange_rate_to_base' => null,
        ]);

        $customer = $this->createCustomer('exc3');
        [$item, $pack] = $this->createItemWithPack(100.0);

        /// Act & Assert
        $this->expectException(ExchangeRateNotFoundException::class);
        $this->pricingService->getPrice(
            $customer,
            $item->id,
            $pack->id,
            1,
            null,
            $usdCurrency->id,
        );
    }

    public function test_throws_when_no_default_pack_exists(): void
    {
        /// Arrange
        $this->createBaseCurrency();
        $customer = $this->createCustomer('exc4');
        $item = Item::create([
            'code' => 'NOPACK-' . uniqid(),
            'name_en' => 'Item without packs',
            'name_ar' => 'بدون عبوات',
            'is_inactive' => false,
        ]);

        /// Act & Assert
        $this->expectException(ItemNotFoundException::class);
        $this->pricingService->calculatePrice($item, $customer);
    }
}
