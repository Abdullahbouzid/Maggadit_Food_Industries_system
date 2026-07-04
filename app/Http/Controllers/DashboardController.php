<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
                ]
            ],
            'staff' => [
                'title' => 'staff data definitions',
                'icon' => 'users',
                'items' => [
                    ['label' => 'Devices', 'icon' => 'phone', 'route' => route('devices.index'), 'disabled' => false],
                    ['label' => 'personal information', 'icon' => 'user', 'route' => '#', 'disabled' => false],
                    ['label' => 'employment details', 'icon' => 'briefcase', 'route' => '#', 'disabled' => false],
                    ['label' => 'attendance schema', 'icon' => 'calendar-check', 'route' => '#', 'disabled' => false],
                ]
            ],
            'customer' => [
                'title' => 'customer data definitions',
                'icon' => 'users',
                'items' => [
                    ['label' => 'customer demographics', 'icon' => 'user-plus', 'route' => '#', 'disabled' => false],
                    ['label' => 'contact information', 'icon' => 'phone', 'route' => '#', 'disabled' => false],
                    ['label' => 'loyalty & segments', 'icon' => 'award', 'route' => '#', 'disabled' => false],
                ]
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
                ]
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
                ]
            ],
            'mobile' => [
                'title' => 'mobile customization',
                'icon' => 'smartphone',
                'items' => [
                    ['label' => 'UI layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                    ['label' => 'offline forms', 'icon' => 'save', 'route' => '#', 'disabled' => false],
                    ['label' => 'push notification schemas', 'icon' => 'bell', 'route' => '#', 'disabled' => false],
                ]
            ],
            'display' => [
                'title' => 'display units',
                'icon' => 'monitor',
                'items' => [
                    ['label' => 'screen layouts', 'icon' => 'layout', 'route' => '#', 'disabled' => false],
                    ['label' => 'widget definitions', 'icon' => 'sliders', 'route' => '#', 'disabled' => false],
                    ['label' => 'real-time dashboards', 'icon' => 'monitor', 'route' => '#', 'disabled' => false],
                ]
            ],
        ];

        // Helper function to check if table/column exists (since we might not have Schema facade imported in all contexts)
        if (!function_exists('Schema')) {
            function Schema() {
                return app(\Illuminate\Database\Schema\Builder::class);
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
                'label' => 'الطلبات'
            ],
            'sales' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'dollar-sign',
                'label' => 'المبيعات'
            ],
            'visitors' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'users',
                'label' => 'الزوار'
            ],
            'earnings' => [
                'value' => 0,
                'change' => 0,
                'change_percent' => 0,
                'trend' => 'neutral',
                'icon' => 'credit-card',
                'label' => 'الأرباح'
            ]
        ];

        try {
            // Try to get order count
            // Assuming there might be an Order model or similar
            try {
                if (class_exists('App\Models\Order')) {
                    $orderModel = new \App\Models\Order();
                    $table = $orderModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentOrders = $orderModel->whereDate('created_at', today())->count();
                        $previousOrders = $orderModel->whereDate('created_at', yesterday())->count();
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
                    $invoiceModel = new \App\Models\Invoice();
                    $table = $invoiceModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentOrders = $invoiceModel->whereDate('created_at', today())->count();
                        $previousOrders = $invoiceModel->whereDate('created_at', yesterday())->count();
                        $stats['orders']['value'] = $currentOrders;
                        $stats['orders']['change'] = $currentOrders - $previousOrders;
                        $stats['orders']['change_percent'] = $previousOrders > 0 ?
                            (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;
                        $stats['orders']['trend'] = $currentOrders > $previousOrders ? 'up' :
                                                   ($currentOrders < $previousOrders ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not fetch order statistics: " . $e->getMessage());
            }

            // Try to get sales/total amount
            try {
                $salesValue = 0;
                $previousSales = 0;

                if (class_exists('App\Models\Order')) {
                    $orderModel = new \App\Models\Order();
                    $table = $orderModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        if (Schema()->hasColumn($table, 'total_amount')) {
                            $currentSales = $orderModel->whereDate('created_at', today())->sum('total_amount');
                            $previousSales = $orderModel->whereDate('created_at', yesterday())->sum('total_amount');
                        }
                        // Alternative: if there's an amount column
                        elseif (Schema()->hasColumn($table, 'amount')) {
                            $currentSales = $orderModel->whereDate('created_at', today())->sum('amount');
                            $previousSales = $orderModel->whereDate('created_at', yesterday())->sum('amount');
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
                    $invoiceModel = new \App\Models\Invoice();
                    $table = $invoiceModel->getTable();
                    if ($table && Schema()->hasTable($table) && Schema()->hasColumn($table, 'total_amount')) {
                        $currentSales = $invoiceModel->whereDate('created_at', today())->sum('total_amount');
                        $previousSales = $invoiceModel->whereDate('created_at', yesterday())->sum('total_amount');
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
                    $orderItemModel = new \App\Models\OrderItem();
                    $table = $orderItemModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'price') &&
                        Schema()->hasColumn($table, 'quantity')) {
                        $currentSales = $orderItemModel->whereDate('created_at', today())
                                                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                                        ->sum(DB::raw('order_items.price * order_items.quantity'));
                        $previousSales = $orderItemModel->whereDate('created_at', yesterday())
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
                Log::warning("Could not fetch sales statistics: " . $e->getMessage());
            }

            // Try to get visitor count (this is trickier - might need to check logs or sessions)
            try {
                // Option 1: If there's a visitor tracking model
                if (class_exists('App\Models\VisitorLog')) {
                    $visitorModel = new \App\Models\VisitorLog();
                    $table = $visitorModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentVisitors = $visitorModel->whereDate('created_at', today())->countDistinct('ip_address');
                        $previousVisitors = $visitorModel->whereDate('created_at', yesterday())->countDistinct('ip_address');
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
                    $userModel = new \App\Models\User();
                    $table = $userModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'last_login_at')) {
                        $currentVisitors = $userModel->whereDate('last_login_at', today())->count();
                        $previousVisitors = $userModel->whereDate('last_login_at', yesterday())->count();
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
                    $customerModel = new \App\Models\Customer();
                    $table = $customerModel->getTable();
                    if ($table && Schema()->hasTable($table)) {
                        $currentVisitors = $customerModel->whereDate('created_at', today())->count();
                        $previousVisitors = $customerModel->whereDate('created_at', yesterday())->count();
                        $stats['visitors']['value'] = $currentVisitors;
                        $stats['visitors']['change'] = $currentVisitors - $previousVisitors;
                        $stats['visitors']['change_percent'] = $previousVisitors > 0 ?
                            (($currentVisitors - $previousVisitors) / $previousVisitors) * 100 : 0;
                        $stats['visitors']['trend'] = $currentVisitors > $previousVisitors ? 'up' :
                                                     ($currentVisitors < $previousVisitors ? 'down' : 'neutral');
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not fetch visitor statistics: " . $e->getMessage());
            }

            // Try to get earnings (might be same as sales or from payments)
            try {
                // First try: if there's a payment model with amount
                if (class_exists('App\Models\Payment')) {
                    $paymentModel = new \App\Models\Payment();
                    $table = $paymentModel->getTable();
                    if ($table && Schema()->hasTable($table) &&
                        Schema()->hasColumn($table, 'amount')) {
                        $currentEarnings = $paymentModel->whereDate('created_at', today())->sum('amount');
                        $previousEarnings = $paymentModel->whereDate('created_at', yesterday())->sum('amount');
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
                Log::warning("Could not fetch earnings statistics: " . $e->getMessage());
                // Fallback to sales value
                $stats['earnings']['value'] = $stats['sales']['value'];
                $stats['earnings']['change'] = $stats['sales']['change'];
                $stats['earnings']['change_percent'] = $stats['sales']['change_percent'];
                $stats['earnings']['trend'] = $stats['sales']['trend'];
            }
        } catch (\Exception $e) {
            Log::error("Error calculating dashboard statistics: " . $e->getMessage());
            // Keep default values if everything fails
        }

        return view('dashboard.index', compact('definitions', 'stats'));
    }
}