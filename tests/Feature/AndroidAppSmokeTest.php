<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Smoke test for every customer-facing screen in the NativePHP app.
 *
 * The web dashboard has its own coverage, but the Android build ships these
 * views, so a route that only works in the browser is a release blocker. Each
 * GET screen is requested as a signed-in customer and must not blow up.
 */
class AndroidAppSmokeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (! env('INVOICE_FLOW_TESTS')) {
            $this->markTestSkipped('opt-in: set INVOICE_FLOW_TESTS=1');
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        parent::tearDown();
    }

    private function customer(): Customer
    {
        return Customer::orderBy('id')->firstOrFail();
    }

    private function actingAsCustomer(): Customer
    {
        $customer = $this->customer();

        // The customer screens run behind check.customer.session, which accepts
        // the customer guard.
        auth()->guard('customer')->setUser($customer);

        return $customer;
    }

    #[DataProvider('screenProvider')]
    public function test_app_screen_renders(string $label, callable $url): void
    {
        $this->actingAsCustomer();

        $response = $this->get($url());

        $response->assertStatus(200, "{$label} returned a non-200 status.");

        $this->assertStringNotContainsString(
            'Whoops, looks like something went wrong',
            $response->getContent(),
            "{$label} rendered a Laravel error page."
        );
    }

    public static function proxyRouteProvider(): array
    {
        return [
            'dashboard (token)' => ['customer.proxy.dashboard', true],
            'surveys (token)' => ['customer.proxy.surveys.index', true],
            'organizations (token)' => ['customer.proxy.organizations.index', true],
            'categories (public)' => ['customer.proxy.categories', false],
            'brands (public)' => ['customer.proxy.brands', false],
            'products (public)' => ['customer.proxy.products', false],
            'offers (public)' => ['customer.proxy.offers', false],
            'faqs (public)' => ['customer.proxy.faqs', false],
        ];
    }

    public static function screenProvider(): array
    {
        $orderId = 1;
        $invoiceId = 1;
        $productId = 1;
        $brandId = 1;
        $categoryId = 1;

        return [
            'dashboard' => ['Dashboard', fn () => route('customer.dashboard')],
            'more' => ['More menu', fn () => route('customer.more')],
            'categories' => ['Categories', fn () => route('customer.categories.index')],
            'category show' => ['Category show', fn () => route('customer.categories.show', $categoryId)],
            'brands' => ['Brands', fn () => route('customer.brands')],
            'brand show' => ['Brand show', fn () => route('customer.brands.show', $brandId)],
            'products' => ['Products', fn () => route('customer.products')],
            'product show' => ['Product show', fn () => route('customer.product.show', $productId)],
            'offers' => ['Offers', fn () => route('customer.offers')],
            'cart' => ['Cart', fn () => route('customer.cart')],
            'favorite' => ['Favorite', fn () => route('customer.favorite')],
            'order history' => ['Order history', fn () => route('customer.order_history')],
            'order details' => ['Order details', fn () => route('customer.order_details', $orderId)],
            'my info' => ['My info', fn () => route('customer.my_info')],
            'my products' => ['My products', fn () => route('customer.my_products')],
            'notifications' => ['Notifications', fn () => route('customer.notifications')],
            'returns' => ['Returns', fn () => route('customer.returns')],
            'financial statement' => ['Financial statement', fn () => route('customer.financial_statement')],
            'surveys' => ['Surveys', fn () => route('customer.surveys')],
            'about' => ['About', fn () => route('customer.about')],
            'contact us' => ['Contact us', fn () => route('customer.contact')],
            'faq' => ['FAQ', fn () => route('customer.faq')],
            'privacy policy' => ['Privacy policy', fn () => route('customer.privacy')],
            'terms' => ['Terms', fn () => route('customer.terms')],
            'contact info' => ['Contact info', fn () => route('customer.contact.info')],
            'change organization' => ['Change organization', fn () => route('customer.change_organization')],
        ];
    }

    /**
     * The proxy routes forward to the live production API.
     *
     * They split into two groups: some guard on a session api_token and must
     * refuse without one, while others are public reads and forward happily.
     * HTTP is faked up front so the suite never touches the real server.
     *
     * @param  bool  $tokenGuarded  true when the route requires api_token
     */
    #[DataProvider('proxyRouteProvider')]
    public function test_proxy_routes_return_json(string $routeName, bool $tokenGuarded): void
    {
        $this->actingAsCustomer();

        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []], 200),
        ]);

        // A token-guarded proxy must refuse rather than forward without a token.
        if ($tokenGuarded) {
            $this->get(route($routeName))->assertStatus(401);
        }

        $this->withSession(['api_token' => 'test-token']);

        $response = $this->get(route($routeName));

        $response->assertStatus(200, "{$routeName} did not forward successfully.");
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * The packaged Android app runs on 127.0.0.1 and must not depend on a CDN
     * being reachable, or every screen renders unstyled.
     */
    public function test_app_views_do_not_load_tailwind_from_a_cdn(): void
    {
        $offenders = [];

        foreach (glob(resource_path('views/app_android/**/*.blade.php')) as $file) {
            if (str_contains((string) file_get_contents($file), 'cdn.tailwindcss.com')) {
                $offenders[] = str_replace(resource_path('views/'), '', $file);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Tailwind must come from the Vite build, not the CDN: '.implode(', ', $offenders)
        );
    }

    /**
     * Every asset() path in an app view must exist, otherwise the APK shows
     * broken images that never get noticed on a dev machine with the file.
     */
    public function test_referenced_static_assets_exist(): void
    {
        $missing = [];

        foreach (glob(resource_path('views/app_android/**/*.blade.php')) as $file) {
            preg_match_all("/asset\(['\"]([^'\"]+)['\"]\)/", (string) file_get_contents($file), $m);

            foreach ($m[1] as $asset) {
                $path = public_path($asset);

                if (! file_exists($path)) {
                    $missing[] = $asset.' ('.str_replace(resource_path('views/'), '', $file).')';
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)), 'Missing assets: '.implode(', ', $missing));
    }
}