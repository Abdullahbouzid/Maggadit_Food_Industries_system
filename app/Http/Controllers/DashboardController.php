<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerFavorite;
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
        ];

        try {
            // Try to get order count
            // Assuming there might be an Order model or similar
            try {
                if (class_exists('App\Models\Order')) {
                    $orderModel = new Order;
                    $table = $orderModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentOrders = $orderModel->whereDate('created_at', Carbon::today()->toDateString())->count();
                        $previousOrders = $orderModel->whereDate('created_at', Carbon::yesterday()->toDateString())->count();
                        $stats['orders']['value'] = $currentOrders;
                        $stats['orders']['change'] = $currentOrders - $previousOrders;
                        $stats['orders']['change_percent'] = $previousOrders > 0 ?
                            (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;
                        $stats['orders']['trend'] = $currentOrders > $previousOrders ? 'up' :
                                                   ($currentOrders < $previousOrders ? 'down' : 'neutral');
                    }
                }
                // Fallback: try to count invoices or sales orders
                elseif (class_exists('App\Models\Invoice')) {
                    $invoiceModel = new Invoice;
                    $table = $invoiceModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentOrders = $invoiceModel->whereDate('created_at', Carbon::today())->count();
                        $previousOrders = $invoiceModel->whereDate('created_at', Carbon::yesterday())->count();
                        $stats['orders']['value'] = $currentOrders;
                        $stats['orders']['change'] = $currentOrders - $previousOrders;
                        $stats['orders']['change_percent'] = $previousOrders > 0 ?
                            (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;
                        $stats['orders']['trend'] = $currentOrders > $previousOrders ? 'up' :
                                                   ($currentOrders < $previousOrders ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch order statistics: '.$e->getMessage());
            }

            // Try to get sales/total amount
            try {
                $salesValue = 0;
                $previousSales = 0;

                if (class_exists('App\Models\Order')) {
                    $orderModel = new Order;
                    $table = $orderModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        if (Schema()->hasColumn($table, 'total_amount')) {
                            $currentSales = $orderModel->whereDate('created_at', Carbon::today()->toDateString())->sum('total_amount');
                            $previousSales = $orderModel->whereDate('created_at', Carbon::yesterday()->toDateString())->sum('total_amount');
                        }
                        // Alternative: if there's an amount column
                        elseif (Schema()->hasColumn($table, 'amount')) {
                            $currentSales = $orderModel->whereDate('created_at', Carbon::today()->toDateString())->sum('amount');
                            $previousSales = $orderModel->whereDate('created_at', Carbon::yesterday()->toDateString())->sum('amount');
                        }

                        if ($currentSales > 0 || $previousSales > 0) {
                            $stats['sales']['value'] = round($currentSales, 2);
                            $stats['sales']['change'] = round($currentSales - $previousSales, 2);
                            $stats['sales']['change_percent'] = $previousSales > 0 ?
                                (($currentSales - $previousSales) / $previousSales) * 100 : 0;
                            $stats['sales']['trend'] = $currentSales > $previousSales ? 'up' :
                                                      ($currentSales < $previousSales ? 'down' : 'neutral');
                        }
                    }
                }
                // Fallback: try to get from invoices
                elseif (class_exists('App\Models\Invoice')) {
                    $invoiceModel = new Invoice;
                    $table = $invoiceModel->getTable();
                    if ($table && Schema()->hasTable($table) && Schema()->hasColumn($table, 'total_amount')) {
                        $currentSales = $invoiceModel->whereDate('created_at', Carbon::today()->toDateString())->sum('total_amount');
                        $previousSales = $invoiceModel->whereDate('created_at', Carbon::yesterday()->toDateString())->sum('total_amount');
                        $stats['sales']['value'] = round($currentSales, 2);
                        $stats['sales']['change'] = round($currentSales - $previousSales, 2);
                        $stats['sales']['change_percent'] = $previousSales > 0 ?
                            (($currentSales - $previousSales) / $previousSales) * 100 : 0;
                        $stats['sales']['trend'] = $currentSales > $previousSales ? 'up' :
                                                  ($currentSales < $previousSales ? 'down' : 'neutral');
                    }
                }
                // Last resort: estimate from items sold (if we have order items)
                elseif (class_exists('App\Models\OrderItem')) {
                    $orderItemModel = new OrderItem;
                    $table = $orderItemModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'price') &&
                        Schema()->hasColumn($table, 'quantity')) {
                        $currentSales = $orderItemModel->whereDate('created_at', Carbon::today()->toDateString())
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->sum(DB::raw('order_items.price * order_items.quantity'));
                        $previousSales = $orderItemModel->whereDate('created_at', Carbon::yesterday()->toDateString())
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->sum(DB::raw('order_items.price * order_items.quantity'));
                        $stats['sales']['value'] = round($currentSales, 2);
                        $stats['sales']['change'] = round($currentSales - $previousSales, 2);
                        $stats['sales']['change_percent'] = $previousSales > 0 ?
                            (($currentSales - $previousSales) / $previousSales) * 100 : 0;
                        $stats['sales']['trend'] = $currentSales > $previousSales ? 'up' :
                                                  ($currentSales < $previousSales ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch sales statistics: '.$e->getMessage());
            }

            // Try to get visitor count (this is trickier - might need to check logs or sessions)
            try {
                // Option 1: If there's a visitor tracking model
                if (class_exists('App\Models\VisitorLog')) {
                    $visitorModel = new VisitorLog;
                    $table = $visitorModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentVisitors = $visitorModel->whereDate('created_at', Carbon::today())->countDistinct('ip_address');
                        $previousVisitors = $visitorModel->whereDate('created_at', Carbon::yesterday())->countDistinct('ip_address');
                        $stats['visitors']['value'] = $currentVisitors;
                        $stats['visitors']['change'] = $currentVisitors - $previousVisitors;
                        $stats['visitors']['change_percent'] = $previousVisitors > 0 ?
                            (($currentVisitors - $previousVisitors) / $previousVisitors) * 100 : 0;
                        $stats['visitors']['trend'] = $currentVisitors > $previousVisitors ? 'up' :
                                                     ($currentVisitors < $previousVisitors ? 'down' : 'neutral');
                    }
                }
                // Option 2: Use unique customers who logged in today
                elseif (class_exists('App\Models\User')) {
                    $userModel = new User;
                    $table = $userModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'last_login_at')) {
                        $currentVisitors = $userModel->whereDate('last_login_at', Carbon::today())->count();
                        $previousVisitors = $userModel->whereDate('last_login_at', Carbon::yesterday())->count();
                        $stats['visitors']['value'] = $currentVisitors;
                        $stats['visitors']['change'] = $currentVisitors - $previousVisitors;
                        $stats['visitors']['change_percent'] = $previousVisitors > 0 ?
                            (($currentVisitors - $previousVisitors) / $previousVisitors) * 100 : 0;
                        $stats['visitors']['trend'] = $currentVisitors > $previousVisitors ? 'up' :
                                                     ($currentVisitors < $previousVisitors ? 'down' : 'neutral');
                    }
                    // Fallback: just count active users
                    else {
                        $currentVisitors = $userModel->where('active', true)->count();
                        $previousVisitors = $currentVisitors; // Assume same for simplicity
                        $stats['visitors']['value'] = $currentVisitors;
                        $stats['visitors']['change'] = 0;
                        $stats['visitors']['change_percent'] = 0;
                        $stats['visitors']['trend'] = 'neutral';
                    }
                }
                // Option 3: Use customer count as proxy
                elseif (class_exists('App\Models\Customer')) {
                    $customerModel = new Customer;
                    $table = $customerModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentVisitors = $customerModel->whereDate('created_at', Carbon::today())->count();
                        $previousVisitors = $customerModel->whereDate('created_at', Carbon::yesterday())->count();
                        $stats['visitors']['value'] = $currentVisitors;
                        $stats['visitors']['change'] = $currentVisitors - $previousVisitors;
                        $stats['visitors']['change_percent'] = $previousVisitors > 0 ?
                            (($currentVisitors - $previousVisitors) / $previousVisitors) * 100 : 0;
                        $stats['visitors']['trend'] = $currentVisitors > $previousVisitors ? 'up' :
                                                     ($currentVisitors < $previousVisitors ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch visitor statistics: '.$e->getMessage());
            }

            // Try to get earnings (might be same as sales or from payments)
            try {
                // First try: if there's a payment model with amount
                if (class_exists('App\Models\Payment')) {
                    $paymentModel = new Payment;
                    $table = $paymentModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'amount')) {
                        $currentEarnings = $paymentModel->whereDate('created_at', Carbon::today())->sum('amount');
                        $previousEarnings = $paymentModel->whereDate('created_at', Carbon::yesterday())->sum('amount');
                        $stats['earnings']['value'] = round($currentEarnings, 2);
                        $stats['earnings']['change'] = round($currentEarnings - $previousEarnings, 2);
                        $stats['earnings']['change_percent'] = $previousEarnings > 0 ?
                            (($currentEarnings - $previousEarnings) / $previousEarnings) * 100 : 0;
                        $stats['earnings']['trend'] = $currentEarnings > $previousEarnings ? 'up' :
                                                     ($currentEarnings < $previousEarnings ? 'down' : 'neutral');
                    }
                }
                // Second try: same as sales (if revenue = sales)
                else {
                    // Use the same value as sales for now
                    $stats['earnings']['value'] = $stats['sales']['value'];
                    $stats['earnings']['change'] = $stats['sales']['change'];
                    $stats['earnings']['change_percent'] = $stats['sales']['change_percent'];
                    $stats['earnings']['trend'] = $stats['sales']['trend'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch earnings statistics: '.$e->getMessage());
                // Fallback to sales value
                $stats['earnings']['value'] = $stats['sales']['value'];
                $stats['earnings']['change'] = $stats['sales']['change'];
                $stats['earnings']['change_percent'] = $stats['sales']['change_percent'];
                $stats['earnings']['trend'] = $stats['sales']['trend'];
            }

            // Calculate items count
            try {
                if (class_exists('App\Models\Item')) {
                    $itemsModel = new Item;
                    $table = $itemsModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentItems = $itemsModel->where('is_inactive', false)->count(); // Count only active items
                        $previousItems = $itemsModel->where('is_inactive', false) // We could use yesterday's count but let's keep it simple for now
                            ->whereDate('created_at', '<=', Carbon::today()->subDay())
                            ->count(); // Items created before today

                        $stats['items_count']['value'] = $currentItems;
                        $stats['items_count']['change'] = $currentItems - $previousItems; // Items added today
                        $stats['items_count']['change_percent'] = $previousItems > 0 ?
                            (($currentItems - $previousItems) / $previousItems) * 100 : 0;
                        $stats['items_count']['trend'] = $currentItems > $previousItems ? 'up' :
                                                   ($currentItems < $previousItems ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch items count statistics: '.$e->getMessage());
            }

            // Calculate warehouses count
            try {
                if (class_exists('App\Models\Warehouse')) {
                    $warehousesModel = new Warehouse;
                    $table = $warehousesModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $stats['warehouses_count']['value'] = $warehousesModel->count();
                        $stats['warehouses_count']['change'] = 0;
                        $stats['warehouses_count']['change_percent'] = 0;
                        $stats['warehouses_count']['trend'] = 'neutral';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch warehouses count statistics: '.$e->getMessage());
            }

            // Calculate customers count
            try {
                if (class_exists('App\Models\Customer')) {
                    $customersModel = new Customer;
                    $table = $customersModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $stats['customers_count']['value'] = $customersModel->count();
                        $stats['customers_count']['change'] = 0;
                        $stats['customers_count']['change_percent'] = 0;
                        $stats['customers_count']['trend'] = 'neutral';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch customers count statistics: '.$e->getMessage());
            }

            // Calculate packs count
            try {
                if (class_exists('App\Models\ItemPack')) {
                    $packsModel = new ItemPack;
                    $table = $packsModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $stats['packs_count']['value'] = $packsModel->count();
                        $stats['packs_count']['change'] = 0;
                        $stats['packs_count']['change_percent'] = 0;
                        $stats['packs_count']['trend'] = 'neutral';
                        $stats['packs_count']['label'] = 'Packs';
                        $stats['packs_count']['icon'] = 'box';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch packs count statistics: '.$e->getMessage());
            }

            // Calculate new customers today
            try {
                if (class_exists('App\Models\Customer')) {
                    $customersModel = new Customer;
                    $table = $customersModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $todayStart = Carbon::today()->startOfDay();
                        $todayEnd = Carbon::today()->endOfDay();
                        $yesterdayStart = Carbon::yesterday()->startOfDay();
                        $yesterdayEnd = Carbon::yesterday()->endOfDay();

                        $current = $customersModel->whereBetween('created_at', [$todayStart, $todayEnd])->count();
                        $previous = $customersModel->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();

                        $stats['new_customers_today']['value'] = $current;
                        $stats['new_customers_today']['change'] = $current - $previous;
                        $stats['new_customers_today']['change_percent'] = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                        $stats['new_customers_today']['trend'] = $current > $previous ? 'up' : ($current < $previous ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch new customers today statistics: '.$e->getMessage());
            }

            // Calculate new customers this month
            try {
                if (class_exists('App\Models\Customer')) {
                    $customersModel = new Customer;
                    $table = $customersModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $monthStart = Carbon::now()->startOfMonth();
                        $monthEnd = Carbon::now()->endOfMonth();
                        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
                        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

                        $current = $customersModel->whereBetween('created_at', [$monthStart, $monthEnd])->count();
                        $previous = $customersModel->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

                        $stats['new_customers_this_month']['value'] = $current;
                        $stats['new_customers_this_month']['change'] = $current - $previous;
                        $stats['new_customers_this_month']['change_percent'] = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
                        $stats['new_customers_this_month']['trend'] = $current > $previous ? 'up' : ($current < $previous ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch new customers this month statistics: '.$e->getMessage());
            }

            // Prepare data for new customers daily chart (last 7 days)
            try {
                if (class_exists('App\Models\Customer')) {
                    $customersModel = new Customer;
                    $table = $customersModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $labels = [];
                        $data = [];
                        for ($i = 6; $i >= 0; $i--) {
                            $day = Carbon::now()->subDays($i)->startOfDay();
                            $label = $day->format('M d'); // e.g., Jan 01
                            $labels[] = $label;
                            $count = $customersModel->whereDate('created_at', $day)->count();
                            $data[] = (int) $count;
                        }
                        $stats['new_customers_daily_chart']['labels'] = $labels;
                        $stats['new_customers_daily_chart']['data'] = $data;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch new customers daily chart data: '.$e->getMessage());
            }

            // Prepare data for new customers monthly chart (last 12 months)
            try {
                if (class_exists('App\Models\Customer')) {
                    $customersModel = new Customer;
                    $table = $customersModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $labels = [];
                        $data = [];
                        for ($i = 11; $i >= 0; $i--) {
                            $month = Carbon::now()->subMonths($i)->startOfMonth();
                            $label = $month->format('M Y'); // e.g., Jan 2026
                            $labels[] = $label;
                            $count = $customersModel->whereBetween('created_at', [$month->startOfMonth(), $month->endOfMonth()])->count();
                            $data[] = (int) $count;
                        }
                        $stats['new_customers_monthly_chart']['labels'] = $labels;
                        $stats['new_customers_monthly_chart']['data'] = $data;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch new customers monthly chart data: '.$e->getMessage());
            }
            try {
                if (class_exists('App\Models\CustomerFavorite') && class_exists('App\Models\Item')) {
                    // Get top 5 most favorited items with their details
                    $topFavorites = CustomerFavorite::select('item_id', DB::raw('count(*) as favorite_count'))
                        ->groupBy('item_id')
                        ->orderByDesc('favorite_count')
                        ->limit(5)
                        ->get();

                    $topItems = [];
                    $labels = [];
                    $data = [];
                    foreach ($topFavorites as $fav) {
                        $item = Item::where('id', $fav->item_id)->first();
                        if ($item) {
                            $topItems[] = [
                                'id' => $item->id,
                                'name_en' => $item->name_en,
                                'name_ar' => $item->name_ar,
                                'image' => $item->getMainImageUrlAttribute(),
                                'favorite_count' => (int) $fav->favorite_count,
                            ];
                            $labels[] = $item->name_ar;
                            $data[] = (int) $fav->favorite_count;
                        }
                    }

                    $stats['top_favorite_items']['items'] = $topItems;
                    $stats['favorites_chart']['labels'] = $labels;
                    $stats['favorites_chart']['data'] = $data;
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch top favorite items statistics: '.$e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error calculating dashboard statistics: '.$e->getMessage());
            // Keep default values if everything fails
        }

        return view('dashboard.index', compact('definitions', 'stats'));
    }
}
