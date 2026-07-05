<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        // فلتر البحث بالعنوان أو النوع
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('type', 'like', '%' . $request->search . '%');
            });
        }

        // فلتر النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // تحديد عدد النتائج في الصفحة
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $bannersPaginated = $query->paginate($query->count() > 0 ? $query->count() : 1);
            $bannersPaginated->setPath(route('banners.index'));
        } else {
            $bannersPaginated = $query->paginate((int) $perPage);
        }

        // إحصائيات البطاقات
        $totalBanners = Banner::count();
        $shownInApp = Banner::where('show_in_customer_app', true)->count();
        $hiddenInApp = Banner::where('show_in_customer_app', false)->count();
        $typesCount = Banner::select('type')->distinct()->count();

        return view('dashboard.data_definition.customer_app.banner.index', compact(
            'bannersPaginated',
            'totalBanners',
            'shownInApp',
            'hiddenInApp',
            'typesCount'
        ));
    }

    public function create()
    {
        return view('dashboard.data_definition.customer_app.banner.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type'        => 'required|in:home,category,product,promo,custom',
            'url'         => 'nullable|url|max:500',
            'show_in_customer_app' => 'boolean',
            'order'       => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['title', 'type', 'url', 'show_in_customer_app', 'order']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'uploads');
            $data['image'] = $imagePath;
        }

        Banner::create($data);

        return redirect()->route('banners.index')
            ->with('success', 'تم إنشاء البنر بنجاح.');
    }

    public function show(Banner $banner)
    {
        return view('dashboard.data_definition.customer_app.banner.show', compact('banner'));
    }

    public function edit(Banner $banner)
    {
        return view('dashboard.data_definition.customer_app.banner.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type'        => 'required|in:home,category,product,promo,custom',
            'url'         => 'nullable|url|max:500',
            'show_in_customer_app' => 'boolean',
            'order'       => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['title', 'type', 'url', 'show_in_customer_app', 'order']);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            if ($banner->image && Storage::disk('uploads')->exists($banner->image)) {
                Storage::disk('uploads')->delete($banner->image);
            }
            $imagePath = $request->file('image')->store('banners', 'uploads');
            $data['image'] = $imagePath;
        }

        $banner->update($data);

        return redirect()->route('banners.index')
            ->with('success', 'تم تحديث البنر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('uploads')->exists($banner->image)) {
            Storage::disk('uploads')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('banners.index')
            ->with('success', 'تم حذف البنر بنجاح.');
    }

    public function export(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $banners = $query->get();

        $fileName = 'banners_' . date('Y-m-d_H-i') . '.csv';
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['ID', 'Title', 'Image URL', 'Type', 'URL', 'Show in App', 'Order', 'Created At']);

        foreach ($banners as $banner) {
            fputcsv($handle, [
                $banner->id,
                $banner->title,
                $banner->image ? asset('uploads/' . $banner->image) : '',
                $banner->type,
                $banner->url,
                $banner->show_in_customer_app ? 'Yes' : 'No',
                $banner->order,
                $banner->created_at,
            ]);
        }

        fclose($handle);
        exit;
    }
}