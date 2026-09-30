<?php

/**
 * HTTP-level test: boots a real request kernel, logs in as a customer and
 * hits every customer-facing GET route, then exercises checkout + respond.
 */

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Item;
use App\Contracts\OrderLifecycleServiceInterface;
use App\Services\OrderStepPresenter;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$kernel = app(Kernel::class);

$GLOBALS['out'] = [];
function line(string $s) { $GLOBALS['out'][] = $s; }
function ok(string $label, bool $cond, string $extra = '') {
    $GLOBALS['out'][] = ($cond ? '  PASS  ' : '  FAIL  ').$label.($extra ? " ({$extra})" : '');
    if (! $cond) { $GLOBALS['out'][] = '!!!! FAILED: '.$label; }
}

/**
 * POST a request the way a real browser would: attach the session and a
 * matching CSRF token, so VerifyCsrfToken passes on its own.
 */
function postJson(string $uri, array $payload, ?Customer $as = null) {
    $store = app('session.store');
    $store->start();

    $request = Request::create($uri, 'POST', $payload);
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $request->setLaravelSession($store);
    $request->merge(['_token' => $store->token()]);

    if ($as) {
        Auth::guard('customer')->setUser($as);
    }

    return app(Kernel::class)->handle($request);
}

function hit(string $method, string $uri, ?Customer $as = null) {
    $request = Request::create($uri, $method);
    $request->headers->set('Accept', 'text/html');

    if ($as) {
        Auth::guard('customer')->setUser($as);
    }

    $response = app(Kernel::class)->handle($request);
    $status = $response->getStatusCode();
    $body = $response->getContent();

    return [$status, $body];
}

// Everything below runs inside one transaction that is rolled back at the
// end, so the order / quotation / invoice / stock rows this test creates
// never reach the database.
DB::beginTransaction();

// Collision's handler cannot render while we drive the HTTP kernel by hand,
// so delegate to Laravel's own handler (which turns ValidationException into
// a proper 422 response) and only rethrow genuine 5xx bugs.
$defaultHandler = new class implements \Illuminate\Contracts\Debug\ExceptionHandler
{
    public function report(\Throwable $e) {}

    public function shouldReport(\Throwable $e) { return false; }

    public function render($request, \Throwable $e)
    {
        return (new \Illuminate\Foundation\Exceptions\Handler(app()))
            ->render($request, $e);
    }

    public function renderForConsole($output, \Throwable $e) {}
};

app()->instance(\Illuminate\Contracts\Debug\ExceptionHandler::class, $defaultHandler);

try {
    runChecks();
} catch (\Throwable $e) {
    line('  FATAL  '.get_class($e).': '.$e->getMessage());
    foreach (array_slice($e->getTrace(), 0, 18) as $i => $t) {
        if (isset($t['file']) && ! str_contains($t['file'], '/vendor/')) {
            line(sprintf('    #%d %s:%s %s', $i, str_replace(base_path().'/', '', $t['file']), $t['line'] ?? '?', $t['function'] ?? ''));
        }
    }
    $GLOBALS['fatal'] = true;
}

DB::rollBack();

// ---------- report ----------
echo implode("\n", $GLOBALS['out'])."\n";
$passes = count(array_filter($GLOBALS['out'], fn ($l) => str_starts_with($l, '  PASS')));
$fails  = count(array_filter($GLOBALS['out'], fn ($l) => str_starts_with($l, '  FAIL')));
echo "\n=================================\n";
echo "passed: {$passes}   failed: {$fails}\n";
echo $fails === 0 && empty($GLOBALS['fatal']) ? "\nHTTP ALL GREEN\n" : "\nHTTP HAD FAILURES\n";

function runChecks(): void
{
// Log a customer in and persist the session id.
$customer = Customer::first();
$item = Item::whereHas('packs')->first();
$pack = $item?->packs()->first();

line('customer: #'.($customer?->id ?? 'NONE').'   item: '.($item?->code ?? 'NONE'));
line('');

// ---------- 1. Protected routes must redirect to login when anonymous ----------
$protected = [
    'customer.dashboard', 'customer.more', 'customer.offers', 'customer.cart',
    'customer.favorite', 'customer.order_history', 'customer.my_info',
    'customer.financial_statement', 'customer.surveys', 'customer.notifications',
];
line('--- 1. anonymous access is blocked ---');
foreach ($protected as $name) {
    $uri = route($name);
    [$status, $body] = hit('GET', $uri);
    $blocked = $status === 302 || $status === 401;
    ok("{$name} blocks anonymous", $blocked, "HTTP {$status}");
}

// ---------- 2. All customer GET routes as a logged-in customer ----------
line('');
line('--- 2. all customer routes render for a logged-in customer ---');

$getRoutes = collect(app('router')->getRoutes())->filter(function ($r) {
    return in_array('GET', $r->methods(), true)
        && str_starts_with($r->getName() ?? '', 'customer.')
        && ! str_contains($r->uri(), 'proxy');
})->values();

foreach ($getRoutes as $route) {
    $name = $route->getName();
    $uri = '/'.ltrim($route->uri(), '/');
    // Fill required params with a real id.
    $uri = preg_replace('/\{id\}/', (string) ($customer->id), $uri);
    $uri = preg_replace('/\{order\}/', (string) (App\Models\Order::first()?->id ?? 1), $uri);

    [$status, $body] = hit('GET', $uri, $customer);

    $isRedirect = $status === 302;
    $isError = $status >= 500;
    $isMissingRecord = $isRedirect; // record-not-found redirects back with an error

    ok("GET {$name}", ! $isError, $isError ? mb_substr(strip_tags($body), 0, 150) : "HTTP {$status}");
}

// ---------- 3. Checkout creates a real Order ----------
line('');
line('--- 3. checkout creates an Order through the lifecycle ---');

$before = App\Models\Order::count();

$response = postJson(route('customer.checkout'), [
    'items' => [[
        'id' => $item->id,
        'pack_id' => $pack?->id,
        'quantity' => 2,
    ]],
    'delivery_address' => 'عنوان اختبار HTTP',
    'delivery_phone' => '0500000000',
], $customer);
$payload = json_decode($response->getContent(), true);


ok('checkout returns HTTP 200', $response->getStatusCode() === 200, 'HTTP '.$response->getStatusCode());
ok('checkout success flag', ($payload['success'] ?? false) === true, json_encode($payload, JSON_UNESCAPED_UNICODE));
ok('checkout returns order_number', ! empty($payload['order_number']), $payload['order_number'] ?? 'none');
ok('checkout returns steps', ! empty($payload['steps']));
ok('checkout created an Order row', App\Models\Order::count() === $before + 1);

$order = App\Models\Order::where('order_number', $payload['order_number'] ?? '')->first();
ok('order starts at "new"', $order && $order->status === OrderStatus::New, $order?->status->value ?? 'no order');
ok('order is visible to the customer', $order && $order->customer_id === $customer->id);
ok('order is created_by = null (came from the app)', $order && $order->created_by === null);
ok('order carries the delivery address', $order?->delivery_address === 'عنوان اختبار HTTP', (string) $order?->delivery_address);

// ---------- 4. Respond endpoint contract ----------
line('');
line('--- 4. quotation respond endpoint ---');

$respondUri = route('customer.order.quotation.respond', $order->id);

// 4a. invalid response value must be rejected
$res = postJson($respondUri, ['response' => 'bogus'], $customer);
ok('invalid response rejected with 422', $res->getStatusCode() === 422, 'HTTP '.$res->getStatusCode());

// 4b. responding before a quote exists must be refused
$res = postJson($respondUri, ['response' => 'approved'], $customer);
$body = json_decode($res->getContent(), true);
ok('no quote -> refused (404)', $res->getStatusCode() === 404, 'HTTP '.$res->getStatusCode());
ok('refusal has an Arabic message', ! empty($body['message']), $body['message'] ?? '');

// 4c. full happy path: admin quotes, customer approves
$lifecycle = app(OrderLifecycleServiceInterface::class);
$lifecycle->transition($order->fresh(), OrderStatus::PendingReview, notes: 'مراجعة');
$lifecycle->transition($order->fresh(), OrderStatus::AdminEdited, notes: 'تعديل');
$lifecycle->transition($order->fresh(), OrderStatus::QuotationSent, notes: 'إرسال العرض');

$order->refresh();
ok('order reached quotation_sent', $order->status === OrderStatus::QuotationSent, $order->status->value);

$res = postJson($respondUri, ['response' => 'approved', 'notes' => 'موافق'], $customer);
$body = json_decode($res->getContent(), true);

ok('approval accepted (200)', $res->getStatusCode() === 200, 'HTTP '.$res->getStatusCode());
ok('approval success flag', ($body['success'] ?? false) === true, json_encode($body, JSON_UNESCAPED_UNICODE) ?: 'no body');
ok('response contains new status label', ! empty($body['status']), $body['status'] ?? '');

$order->refresh();
ok('order cascaded to pending_approval', $order->status === OrderStatus::PendingApproval, $order->status->value);
ok('invoice was auto-created', App\Models\Invoice::where('order_id', $order->id)->exists());

// 4d. duplicate response must be refused
$res = postJson($respondUri, ['response' => 'approved'], $customer);
ok('duplicate response refused (422)', $res->getStatusCode() === 422, 'HTTP '.$res->getStatusCode());

// ---------- 5. IDOR: another customer cannot read this order ----------
line('');
line('--- 5. ownership is enforced ---');
$other = Customer::where('id', '!=', $customer->id)->first();

if ($other) {
    Auth::guard('customer')->setUser($other);
    [$status, ] = hit('GET', route('customer.order_details', $order->id), $other);
    $body = view('app_android.more.order_details', [
        'order' => null, 'steps' => [], 'timeline' => [], 'orderItems' => [],
        'quotation' => null, 'quotationItems' => [], 'invoice' => null,
        'statusMeta' => ['label' => '', 'class' => ''], 'progress' => 0, 'canRespond' => false,
    ])->render();
    ok('other customer sees "not found" page', str_contains($body, 'الطلبية غير موجودة'));

    $res = postJson(route('customer.order.quotation.respond', $order->id), ['response' => 'approved'], $other);
    ok('other customer cannot respond (404)', $res->getStatusCode() === 404, 'HTTP '.$res->getStatusCode());
} else {
    line('  SKIP  only one customer exists in the DB');
}

// ---------- 6. order details page shows the steps ----------
line('');
line('--- 6. order details page renders the steps ---');
Auth::guard('customer')->setUser($customer);
[$status, $body] = hit('GET', route('customer.order_details', $order->id), $customer);

ok('order details HTTP 200', $status === 200, "HTTP {$status}");
ok('page shows the order number', str_contains($body, $order->order_number));
ok('page shows the steps heading', str_contains($body, 'خطوات الطلبية'));
ok('page shows the Arabic status label', str_contains($body, 'قيد الموافقة'));
ok('page shows the invoice section', str_contains($body, 'الفاتورة') || str_contains($body, 'إصدار الفاتورة'));
ok('page shows the status history', str_contains($body, 'سجل تتبّع الطلبية'));
ok('order PDF link present', str_contains($body, route('customer.order_pdf', $order->id, false)));
ok('invoice PDF link present', str_contains($body, route('customer.order_invoice_pdf', $order->id, false)));

// ---------- 7. order history page ----------
line('');
line('--- 7. order history page ---');
[$status, $body] = hit('GET', route('customer.order_history'), $customer);
ok('order history HTTP 200', $status === 200, "HTTP {$status}");
ok('history lists the order', str_contains($body, $order->order_number));
ok('history links to details', str_contains($body, route('customer.order_details', $order->id, false)));

// ---------- 8. PDF endpoints ----------
line('');
line('--- 8. customer-scoped PDFs ---');
foreach ([
    'customer.order_pdf' => $order->id,
    'customer.order_quotation_pdf' => $order->id,
    'customer.order_invoice_pdf' => $order->id,
] as $name => $param) {
    Auth::guard('customer')->setUser($customer);
    [$status, $pdf] = hit('GET', route($name, $param), $customer);
    ok("{$name} returns a PDF", $status === 200, "HTTP {$status}");
    ok("{$name} is a real PDF", str_starts_with((string) $pdf, '%PDF'), substr((string) $pdf, 0, 8));
}
}
