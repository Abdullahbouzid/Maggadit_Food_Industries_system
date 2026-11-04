<?php

/**
 * Smoke test: render every customer-facing route and report pass/fail.
 * Usage: php artisan tinker --execute="require base_path('smoke_customer.php');"
 */

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

$results = [];
$failures = [];

function check(string $label, callable $fn, array &$results, array &$failures): void
{
    try {
        $fn();
        $results[] = ['label' => $label, 'ok' => true, 'error' => null];
    } catch (\Throwable $e) {
        $msg = mb_substr(get_class($e).': '.$e->getMessage(), 0, 260);
        $results[] = ['label' => $label, 'ok' => false, 'error' => $msg];
        $failures[] = $label.' :: '.$msg;
    }
}

$customer = Customer::first();
$order = \App\Models\Order::first();
$invoice = \App\Models\Invoice::first();
$item = \App\Models\Item::first();
$category = \App\Models\Category::first();
$brand = \App\Models\Brand::first();

echo "customer: ".($customer?->id ?? 'NONE')."\n";
echo "order: ".($order?->order_number ?? 'NONE')."\n";

// Log in as the first customer so protected routes resolve.
if ($customer) {
    Auth::guard('customer')->login($customer);
    session(['customer_id' => $customer->id]);
}

$presenter = app(\App\Services\OrderStepPresenter::class);
$steps = $order ? $presenter->steps($order) : [];
$timeline = $order ? $presenter->timeline($order) : [];
$meta = $order ? $presenter->currentStatusMeta($order) : ['label' => '', 'class' => ''];

// In a real HTTP request ShareErrorsFromSession provides $errors; tinker has no middleware.
view()->share('errors', new \Illuminate\Support\ViewErrorBag);

// ---- Public / catalog views (render the view directly) ----
$views = [
    'app_android.login' => [],
    'app_android.register' => [],
    'app_android.customer_dashboard' => [
        'banners' => collect(), 'categories' => collect(), 'brands' => collect(),
        'products' => collect(), 'favoriteItemIds' => [], 'offers' => collect(),
    ],
    'app_android.categories.index' => ['categories' => collect()],
    'app_android.categories.show' => [
        'category' => $category ?? new \App\Models\Category(['name_ar' => 'x']),
        'items' => collect(), 'favoriteItemIds' => [],
    ],
    'app_android.brands.index' => ['brands' => collect()],
    'app_android.brands.show' => [
        'brand' => $brand ?? new \App\Models\Brand(['name' => 'x']),
        'items' => collect(), 'favoriteItemIds' => [],
    ],
    'app_android.products.index' => [
        'items' => collect(), 'favoriteItemIds' => [], 'categories' => collect(), 'brands' => collect(),
    ],
    'app_android.product.show' => [
        'product' => $item ?? new \App\Models\Item(['name_ar' => 'x']),
        'favoriteItemIds' => [],
    ],
    'app_android.offers.index' => ['offers' => collect()],
    'app_android.cart' => [],
    'app_android.favorite' => ['favoriteItems' => []],
    'app_android.more' => ['customer' => $customer],
    'app_android.more.my_info' => ['customer' => $customer],
    'app_android.more.faq' => ['faqsByCategory' => collect()],
    'app_android.more.notifications' => ['notifications' => collect(), 'unreadCount' => 0],
    'app_android.more.surveys' => [],
    'app_android.more.change_organization' => [],
    'app_android.more.contact_us' => [],
    'app_android.more.privacy_policy' => ['policy' => null],
    'app_android.more.financial_statement' => [],
    'app_android.more.order_history' => [
        'rows' => [],
        'stats' => ['all' => 0, 'pending' => 0, 'delivered' => 0],
    ],
    'app_android.more.order_details' => [
        'order' => null, 'orderId' => null, 'steps' => [], 'timeline' => [],
        'orderItems' => [], 'quotation' => null, 'quotationItems' => [],
        'invoice' => null, 'statusMeta' => ['label' => '', 'class' => ''],
        'progress' => 0, 'canRespond' => false,
    ],
    'pdf.order' => ['order' => $order],
    'pdf.quotation' => ['quotation' => \App\Models\Quotation::first()],
    'pdf.invoice' => ['invoice' => $invoice, 'lang' => 'ar'],
];

foreach ($views as $viewName => $data) {
    check("VIEW  {$viewName}", function () use ($viewName, $data) {
        if (! View::exists($viewName)) {
            throw new \RuntimeException('view file not found');
        }
        $html = View::make($viewName, $data)->render();
        if (trim($html) === '') {
            throw new \RuntimeException('rendered empty');
        }
    }, $results, $failures);
}

// ---- Order steps partial with a real order ----
check('PARTIAL order_steps (real order)', function () use ($steps) {
    $html = View::make('app_android.partials.order_steps', ['steps' => $steps])->render();
    if (trim($html) === '') {
        throw new \RuntimeException('rendered empty');
    }
}, $results, $failures);

// ---- Presenter behaviour ----
check('PRESENTER steps are well formed', function () use ($steps) {
    if (! is_array($steps)) {
        throw new \RuntimeException('steps is not an array');
    }
    foreach ($steps as $s) {
        foreach (['key', 'label', 'state'] as $k) {
            if (! isset($s[$k])) {
                throw new \RuntimeException("missing key {$k}");
            }
        }
        if (! in_array($s['state'], ['done', 'current', 'pending', 'rejected'], true)) {
            throw new \RuntimeException('bad state: '.$s['state']);
        }
    }
}, $results, $failures);

// ---- Route integrity: every route() name used in app_android views exists ----
check('ROUTES all referenced route names resolve', function () {
    $defined = collect(Route::getRoutes())->map(fn ($r) => $r->getName())->filter()->flip();

    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(base_path('resources/views/app_android'))
    );

    $missing = [];
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getPathname());
        preg_match_all("/route\(\s*'([^']+)'/", $content, $m);
        foreach ($m[1] as $name) {
            if (! $defined->has($name)) {
                $missing[] = $name.' @ '.$file->getFilename();
            }
        }
    }

    if ($missing) {
        throw new \RuntimeException('missing routes: '.implode(', ', array_unique($missing)));
    }
}, $results, $failures);

// ---- Report ----
echo "\n";
echo "================ SMOKE TEST ================\n";
foreach ($results as $r) {
    echo ($r['ok'] ? "  PASS  " : "  FAIL  ").$r['label']."\n";
    if (! $r['ok']) {
        echo "        ".$r['error']."\n";
    }
}
echo "===========================================\n";
echo 'passed: '.count(array_filter($results, fn ($r) => $r['ok'])).' / '.count($results)."\n";

if ($failures) {
    echo "\nFAILURES:\n- ".implode("\n- ", $failures)."\n";
} else {
    echo "\nALL GREEN\n";
}
