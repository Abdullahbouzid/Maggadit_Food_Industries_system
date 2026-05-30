<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search and filters.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by division
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }

        // Filter by show_in_customer_app
        if ($request->filled('show_status')) {
            $status = $request->show_status;
            if ($status === 'shown') {
                $query->where('show_in_customer_app', true);
            } elseif ($status === 'hidden') {
                $query->where('show_in_customer_app', false);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $categories = $query->get();
            $categoriesPaginated = $query->paginate($categories->count() ?: 1);
            $categoriesPaginated->setPath(route('categories.index'));
        } else {
            $categoriesPaginated = $query->paginate((int) $perPage);
        }

        // Statistics
        $totalCategories = Category::count();
        $shownInApp = Category::where('show_in_customer_app', true)->count();
        $hiddenInApp = Category::where('show_in_customer_app', false)->count();
        $withImage = Category::whereNotNull('image')->count();
        $divisions = Category::distinct()->pluck('division'); // for filter dropdown

        return view('dashboard.data_definition.data_product.category.index', compact(
            'categoriesPaginated', 'totalCategories', 'shownInApp', 'hiddenInApp', 'withImage', 'divisions'
        ));
    }

    /**
     * Show form to create a new category.
     */
    public function create()
    {
        return view('dashboard.data_definition.data_product.category.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:categories,code',
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'show_in_customer_app' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['code', 'name', 'division', 'show_in_customer_app']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }

        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return view('dashboard.data_definition.data_product.category.show', compact('category'));
    }

    /**
     * Show form to edit category.
     */
    public function edit(Category $category)
    {
        return view('dashboard.data_definition.data_product.category.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:categories,code,' . $category->id,
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'show_in_customer_app' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['code', 'name', 'division', 'show_in_customer_app']);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }

        $category->update($data);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete the category and its image.
     */
    public function destroy(Category $category)
    {
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Export categories to CSV.
     */
    public function export(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }
        if ($request->filled('show_status')) {
            $status = $request->show_status;
            if ($status === 'shown') {
                $query->where('show_in_customer_app', true);
            } elseif ($status === 'hidden') {
                $query->where('show_in_customer_app', false);
            }
        }

        $categories = $query->get();

        $fileName = 'categories_' . date('Y-m-d_H-i') . '.csv';
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['ID', 'Code', 'Name', 'Division', 'Show in Customer App', 'Image URL', 'Created At']);

        foreach ($categories as $cat) {
            fputcsv($handle, [
                $cat->id,
                $cat->code,
                $cat->name,
                $cat->division,
                $cat->show_in_customer_app ? 'Yes' : 'No',
                $cat->image ? asset('storage/' . $cat->image) : '',
                $cat->created_at,
            ]);
        }

        fclose($handle);
        exit;
    }
}