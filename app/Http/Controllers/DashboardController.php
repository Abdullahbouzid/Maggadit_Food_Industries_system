<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerFavorite;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemPack;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $definitions = [
            'master' => [
                'title' => 'master data definitions',
                'icon' => 'database',
                'items' => [
                    ['label' => 'Organizations', 'icon' => 'users', 'route' => route('organizations.index'), 'disabled' => false],
                    ['label' => 'Vendors', 'icon' => 'truck', 'route' => route('vendors.index'), 'disabled' => false],
                    ['label' => 'Pack Statuses', 'icon' => 'package', 'route' => route('package-statuses.index'), 'disabled' => false],
                    ['label' => 'Visit Types', 'icon' => 'map-pin', 'route' => '#', 'disabled' => false],
                    ['label' => 'Fields Group', 'icon' => 'layers', 'route' => '#', 'disabled' => false],
                    ['label' => 'Geographical Location Hierarchy', 'icon' => 'map', 'route' => route('geographical.index'), 'disabled' => false],
                    ['label' => 'Sales Order Type', 'icon' => 'truck', 'route' => route('sales-order-types.index'), 'disabled' => false],
                    ['label' => 'Stock Statuses', 'icon' => 'package', 'route' => '#', 'disabled' => false],
                    ['label' => 'Item Model', 'icon' => 'box', 'route' => '#', 'disabled' => false],
                    ['label' => 'Currencies', 'icon' => 'dollar-sign', 'route' => route('currencies.index'), 'disabled' => false],
                    ['label' => 'Currency Rates', 'icon' => 'repeat', 'route' => '#', 'disabled' => false],
                    ['label' => 'Banks', 'icon' => 'bank', 'route' => '#', 'disabled' => false],
                    ['label' => 'Branches', 'icon' => 'building', 'route' => '#', 'disabled' => false],
                    ['label' => 'Tax Type', 'icon' => 'percent', 'route' => '#', 'disabled' => false],
                    ['label' => 'Reasons', 'icon' => 'alert-triangle', 'route' => '#', 'disabled' => false],
                    ['label' => 'Reason Groups', 'icon' => 'list', 'route' => '#', 'disabled' => false],
                ],
            ],
            'staff' => [
                'title' => 'staff data definitions',
                'icon' => 'users',
                'items' => [
                    ['label' => 'Devices', 'icon' => 'phone', 'route' => route('devices.index'), 'disabled' => false],
                    ['label' => 'personal information', 'icon' => 'user', 'route' => '#', 'disabled' => false],
                    ['label' => 'employment details', 'icon' => 'briefcase', 'route' => '#', 'disabled' => false],
                    ['label' => 'attendance schema', 'icon' => 'calendar-check', 'route' => '#', 'disabled' => false],
                ],
            ],
            'customer' => [
                'title' => 'customer data definitions',
                'icon' => 'users',
                'items' => [
                    ['label' => 'customer demographics', 'icon' => 'user-plus', 'route' => '#', 'disabled' => false],
                    ['label' => 'contact information', 'icon' => 'phone', 'route' => '#', 'disabled' => false],
                    ['label' => 'loyalty & segments', 'icon' => 'award', 'route' => '#', 'disabled' => false],
                ],
            ],
            'products' => [
                'title' => 'products data definitions',
                'icon' => 'box',
                'items' => [
                    ['label' => 'product attributes', 'icon' => 'sliders', 'route' => '#', 'disabled' => false],
                    ['label' => 'pricing & tax rules', 'icon' => 'percent', 'route' => '#', 'disabled' => false],
                    ['label' => 'Brands', 'icon' => 'tag', 'route' => route('brands.index'), 'disabled' => false],
                    ['label' => 'Categories', 'icon' => 'layers', 'route' => route('categories.index'), 'disabled' => false],
                    ['label' => 'Item Types', 'icon' => 'box', 'route' => route('item-types.index'), 'disabled' => false],
                    ['label' => 'Units of Measure (UOMs)', 'icon' => 'arrows-alt', 'route' => route('uoms.index'), 'disabled' => false],
                    ['label' => 'Pack Operations', 'icon' => 'shift', 'route' => route('pack-operations.index'), 'disabled' => false],
                    ['label' => 'Pack Groups', 'icon' => 'layers', 'route' => route('pack-groups.index'), 'disabled' => false],
                    ['label' => 'Items', 'icon' => 'box', 'route' => route('items.index'), 'disabled' => false],
                    ['label' => 'Item Packs', 'icon' => 'package', 'route' => route('item-packs.index'), 'disabled' => false],
                    ['label' => 'Item Images', 'icon' => 'image', 'route' => route('item-images.index'), 'disabled' => false],
                    ['label' => 'Item Models', 'icon' => 'box', 'route' => route('item-models.index'), 'disabled' => false],
                ],
            ],
            'warehouse' => [
                'title' => 'warehouse & vehicles',
                'icon' => 'truck',
                'items' => [
                    ['label' => 'states', 'icon' => 'map-pin', 'route' => route('states.index'), 'disabled' => false],
                    ['label' => 'areas', 'icon' => 'map-pin', 'route' => route('areas.index'), 'disabled' => false],
                    ['label' => 'warehouse types', 'icon' => 'warehouse', 'route' => route('warehouse-types.index'), 'disabled' => false],
                    ['label' => 'warehouses', 'icon' => 'warehouse', 'route' => route('warehouses.index'), 'disabled' => false],
                    ['label' => 'vehicle types', 'icon' => 'truck', 'route' => route('vehicle-types.index'), 'disabled' => false],
                    ['label' => 'vehicles', 'icon' => 'truck', 'route' => route('vehicles.index'), 'disabled' => false],
                ],
            ],
            'mobile' => [
                'title' => 'mobile customization',
                'icon' => 'smartphone',
                'items' => [
                    ['label' => 'UI layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                    ['label' => 'offline forms', 'icon' => 'save', 'route' => '#', 'disabled' => false],
                    ['label' => 'push notification schemas', 'icon' => 'bell', 'route' => '#', 'disabled' => false],
                ],
            ],
            'display' => [
                'title' => 'display units',
                'icon' => 'monitor',
                'items' => [
                    ['label' => 'screen layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                    ['label' => 'widget definitions', 'icon' => 'sliders', 'route' => '#', 'disabled' => false],
                    ['label' => 'real-time dashboards', 'icon' => 'monitor', 'route' => '#', 'disabled' => false],
                ],
            ],
        ];

        // Helper function to check if table/column exists (since we might not have Schema facade imported in all contexts)
        if (! function_exists('Schema')) {
            function Schema()
            {
                return app(Builder::class);
            }
        }

        // Initialize statistics with default values
        $stats = [
            'orders' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral', // up, down, neutral
                'icon' => 'shopping-cart',
                'label' => 'الطلبات',
            ],
            'sales' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'dollar-sign',
                'label' => 'المبيعات',
            ],
            'visitors' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'users',
                'label' => 'الزوار',
            ],
            'earnings' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'credit-card',
                'label' => 'الأرباح',
            ],
            'items_count' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'box',
                'label' => 'عدد الاصناف',
            ],
            'top_favorite_items' => [
                'items' => [], // Will store top favorite items
                'icon' => 'heart',
                'label' => 'اكثر المنتجات مفضلة',
            ],
            'warehouses_count' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'box',
                'label' => 'عدد المخازن',
            ],
            'customers_count' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'users',
                'label' => 'عدد العملاء',
            ],
            'packs_count' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'package',
                'label' => 'المونتاجات',
            ],
            'new_customers_today' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'user-plus',
                'label' => 'عملاء جدد اليوم',
            ],
            'new_customers_this_month' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'calendar-plus',
                'label' => 'عملاء جدد هذا الشهر',
            ],
'favorites_chart' => [
                'labels' => [],
                'data' => [],
            ],
            'orders_today' => 0,
            'orders_today_trend' => 0,
            'pending_invoices' => 0,
            'near_deadline' => 0,
            'near_deadline_trend' => 0,
            'pending_returns' => 0,
            'recent_orders' => collect(),
            'low_stock_items' => collect(),
            'near_deadline_invoices' => collect(),
        ];

        try {
            // Compute orders_today and orders_today_trend
            try {
                $currentOrders = Order::whereDate('created_at', Carbon::today()->toDateString())->count();
                $previousOrders = Order::whereDate('created_at', Carbon::yesterday()->toDateString())->count();
                $stats['orders_today'] = $currentOrders;
                $stats['orders_today_trend'] = $previousOrders > 0
                    ? (int) round((($currentOrders - $previousOrders) / $previousOrders) * 100)
                    : 0;
                $stats['recent_orders'] = Order::with(['customer', 'currency'])
                    ->latest()
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Could not fetch orders_today stats: '.$e->getMessage());
            }

            // Compute pending_invoices
            try {
                $pendingInvoicesCount = 0;
                if (class_exists('App\Models\Invoice') && class_exists('App\Enums\InvoiceStatus')) {
                    $pendingInvoicesCount = Invoice::whereNotIn('status', ['paid', 'cancelled'])
                        ->whereDate('created_at', Carbon::today()->toDateString())
                        ->count();
                }
                $stats['pending_invoices'] = $pendingInvoicesCount;
            } catch (\Exception $e) {
                Log::warning('Could not fetch pending_invoices: '.$e->getMessage());
            }

            // Compute near_deadline and near_deadline_invoices
            try {
                $nearDeadlineCount = 0;
                $nearDeadlineInvoices = collect();
                if (class_exists('App\Models\Invoice')) {
                    $cutoffDate = now()->addDays(3);
                    $nearDeadlineInvoices = Invoice::with(['customer', 'currency', 'order.branch'])
                        ->whereIn('status', ['approved', 'partially_paid', 'pending_approval'])
                        ->whereNotNull('payment_deadline_at')
                        ->where(function ($query) use ($cutoffDate) {
                            $query->where('payment_deadline_at', '<=', $cutoffDate)
                                  ->where('payment_deadline_at', '>=', now()->subDays(365)->startOfDay());
                        })
                        ->orderBy('payment_deadline_at')
                        ->limit(10)
                        ->get();
                    $nearDeadlineCount = $nearDeadlineInvoices->count();
                    $currentDeadlineInvoices = Invoice::whereIn('status', ['approved', 'partially_paid', 'pending_approval'])
                        ->whereNotNull('payment_deadline_at')
                        ->where('payment_deadline_at', '<=', Carbon::yesterday()->addDays(3)->endOfDay())
                        ->where('payment_deadline_at', '>=', Carbon::yesterday()->subDays(365)->startOfDay())
                        ->count() ?? 0;
                    $stats['near_deadline_trend'] = $currentDeadlineInvoices > 0
                        ? (int) round((($nearDeadlineCount - $currentDeadlineInvoices) / max($currentDeadlineInvoices, 1)) * 100)
                        : 0;
                }
                $stats['near_deadline'] = $nearDeadlineCount;
                $stats['near_deadline_invoices'] = $nearDeadlineInvoices;
            } catch (\Exception $e) {
                Log::warning('Could not fetch near_deadline stats: '.$e->getMessage());
            }

            // Compute pending_returns
            try {
                if (class_exists('App\Models\ReturnRequest')) {
                    $stats['pending_returns'] = \App\Models\ReturnRequest::where('status', 'pending')->count();
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch pending_returns: '.$e->getMessage());
            }

            // Compute low_stock_items
            try {
                if (class_exists('App\Models\Inventory')) {
                    $stats['low_stock_items'] = Inventory::with(['item', 'warehouse'])
                        ->whereRaw('quantity_on_hand <= reorder_level')
                        ->limit(10)
                        ->get();
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch low_stock_items: '.$e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error calculating dashboard statistics: '.$e->getMessage());
        }

        return view('dashboard.index', compact('definitions', 'stats'));
    }
}
