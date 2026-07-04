<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return view('dashboard.index', compact('definitions'));
    }
}