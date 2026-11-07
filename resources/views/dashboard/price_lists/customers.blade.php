@extends('dashboard.partials.layout')

@section('title', 'ربط قوائم الأسعار بالعملاء')

@section('content')
<div class="nova-actionbar">
    <div>
        <h2 class="nova-actionbar-title">
            <i class="fas fa-users" style="color: var(--nova-primary); margin-right: 0.5rem;"></i>
            ربط قوائم الأسعار بالعملاء
        </h2>
        <p class="nova-page-subtitle" style="margin: 0.25rem 0 0;">تحديد قائمة الأسعار لكل عميل</p>
    </div>
</div>

@if(session('success'))
    <div class="nova-alert success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="nova-card">
    <div class="nova-card-header">
        <div class="nova-row nova-gap-2">
            <div class="nova-col">
                <form method="GET" class="nova-flex nova-gap-2">
                    <input type="text" name="search" class="nova-input" placeholder="بحث عن عميل..." value="{{ request('search') }}">
                    <button type="submit" class="nova-btn nova-btn-primary nova-btn-sm"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="nova-col">
                <select name="price_list_id" class="nova-select" onchange="this.form.submit()">
                    <option value="">كل القوائم</option>
                    @foreach($priceLists as $list)
                        <option value="{{ $list->id }}" {{ request('price_list_id') == $list->id ? 'selected' : '' }}>{{ $list->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['search', 'price_list_id']))
                <div class="nova-col nova-flex nova-items-center">
                    <a href="{{ route('price-lists.customers') }}" class="nova-btn nova-btn-outline nova-btn-sm">
                        <i class="fas fa-times"></i> مسح
                    </a>
                </div>
            @endif
        </div>
    </div>
    <div class="nova-card-body p-0">
        <div class="nova-table-scroll">
            <table class="nova-table">
                <thead>
                    <tr>
                        <th class="nova-text-start">#</th>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>قائمة الأسعار</th>
                        <th class="nova-text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="nova-fw-semibold">{{ $customer->id }}</td>
                            <td><span class="nova-fw-bold">{{ $customer->name }}</span></td>
                            <td><small class="nova-text-muted">{{ $customer->username }}</small></td>
                            <td><small class="nova-text-muted">{{ $customer->email }}</small></td>
                            <td>
                                @if($customer->priceList)
                                    <span class="nova-badge info">{{ $customer->priceList->name }}</span>
                                @else
                                    <span class="nova-text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td class="nova-text-end">
                                <button type="button" class="nova-btn nova-btn-outline nova-btn-sm" onclick="document.getElementById('updatePriceListModal{{ $customer->id }}').classList.add('show')">
                                    <i class="fas fa-edit"></i> تعديل
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="nova-text-center" style="padding: 2rem;">
                                <div>
                                    <div class="nova-stat-icon secondary" style="margin: 0 auto 1rem; width: 48px; height: 48px; font-size: 1.5rem;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5 class="nova-text-muted">لا يوجد عملاء</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="nova-card-footer">
        <div class="nova-flex nova-justify-between nova-items-center nova-flex-wrap nova-gap-2">
            <div class="nova-text-muted nova-fs-sm">
                عرض {{ $customers->firstItem() ?? 0 }} إلى {{ $customers->lastItem() ?? 0 }} من أصل {{ $customers->total() }} سجلاً
            </div>
            <div class="nova-pagination">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
