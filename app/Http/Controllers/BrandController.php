<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * عرض قائمة العلامات التجارية مع البحث والفلاتر.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        // فلتر البحث بالاسم
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // تحديد عدد النتائج في الصفحة
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $brandsPaginated = $query->paginate($query->count() > 0 ? $query->count() : 1);
            $brandsPaginated->setPath(route('brands.index'));
        } else {
            $brandsPaginated = $query->paginate((int) $perPage);
        }

        // إحصائيات البطاقات
        $totalBrands = Brand::count();
        $shownInApp = Brand::where('show_in_customer_app', true)->count();
        $hiddenInApp = Brand::where('show_in_customer_app', false)->count();
        $withLogo = Brand::whereNotNull('logo')->count();

        return view('dashboard.data_definition.data_product.brand.index', compact('brandsPaginated', 'totalBrands', 'shownInApp', 'hiddenInApp', 'withLogo'));
    }

    /**
     * عرض نموذج إضافة علامة تجارية جديدة.
     */
    public function create()
    {
        return view('dashboard.data_definition.data_product.brand.create');
    }

    /**
     * حفظ علامة تجارية جديدة في قاعدة البيانات.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'show_in_customer_app' => 'boolean',
        ]);

        $data = $request->only(['name', 'show_in_customer_app']);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'uploads');
            $data['logo'] = $logoPath;
        }

        Brand::create($data);

        return redirect()->route('brands.index')
            ->with('success', 'تم إنشاء العلامة التجارية بنجاح.');
    }

    /**
     * عرض تفاصيل علامة تجارية محددة.
     */
    public function show(Brand $brand)
    {
        return view('dashboard.data_definition.data_product.brand.show', compact('brand'));
    }

    /**
     * عرض نموذج تعديل علامة تجارية.
     */
    public function edit(Brand $brand)
    {
        return view('dashboard.data_definition.data_product.brand.edit', compact('brand'));
    }

    /**
     * تحديث بيانات علامة تجارية.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,'.$brand->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'show_in_customer_app' => 'boolean',
        ]);

        $data = $request->only(['name', 'show_in_customer_app']);

        if ($request->hasFile('logo')) {
            // حذف الشعار القديم إذا كان موجودًا
            if ($brand->logo && Storage::disk('uploads')->exists($brand->logo)) {
                Storage::disk('uploads')->delete($brand->logo);
            }
            $logoPath = $request->file('logo')->store('brands', 'uploads');
            $data['logo'] = $logoPath;
        }

        $brand->update($data);

        return redirect()->route('brands.index')
            ->with('success', 'تم تحديث العلامة التجارية بنجاح.');
    }

    /**
     * حذف علامة تجارية (مع حذف الشعار من التخزين).
     */
    public function destroy(Brand $brand)
    {
        if ($brand->logo && Storage::disk('uploads')->exists($brand->logo)) {
            Storage::disk('uploads')->delete($brand->logo);
        }
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'تم حذف العلامة التجارية بنجاح.');
    }

    /**
     * تصدير العلامات التجارية إلى ملف CSV.
     */
    public function export(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('show_status')) {
            $status = $request->show_status;
            if ($status === 'shown') {
                $query->where('show_in_customer_app', true);
            } elseif ($status === 'hidden') {
                $query->where('show_in_customer_app', false);
            }
        }

        $brands = $query->get();

        $fileName = 'brands_'.date('Y-m-d_H-i').'.csv';
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');

        // إضافة BOM لدعم UTF-8 في Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['ID', 'Name', 'Logo URL', 'Show in Customer App', 'Created At']);

        foreach ($brands as $brand) {
            fputcsv($handle, [
                $brand->id,
                $brand->name,
                $brand->logo ? asset('storage/'.$brand->logo) : '',
                $brand->show_in_customer_app ? 'Yes' : 'No',
                $brand->created_at,
            ]);
        }

        fclose($handle);
        exit;
    }
}
