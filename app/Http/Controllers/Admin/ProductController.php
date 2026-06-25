<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('products-view'), 403);

        $search = trim((string) $request->input('search'));
        
        $products = Product::when($search !== '', function ($query) use ($search) {
            $query->where('product_code', 'like', "%{$search}%")
                  ->orWhere('medicine_name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(15);

        return view('admin.products.index', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()?->can('products-create'), 403);
        
        $productCode = Product::generateProductCode();
        $units = ['piece', 'tablet', 'capsule', 'bottle', 'vial', 'injection', 'sachet', 'ampoule'];
        
        return view('admin.products.create', compact('productCode', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()?->can('products-create'), 403);

        $validated = $request->validate([
            'product_code' => 'required|string|unique:products,product_code',
            'medicine_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
            'minimum_stock' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
                       ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        abort_unless(auth()->user()?->can('products-edit'), 403);
        
        $units = ['piece', 'tablet', 'capsule', 'bottle', 'vial', 'injection', 'sachet', 'ampoule'];
        
        return view('admin.products.edit', compact('product', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        abort_unless(auth()->user()?->can('products-edit'), 403);

        $validated = $request->validate([
            'product_code' => 'required|string|unique:products,product_code,' . $product->id,
            'medicine_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string',
            'expiry_date' => 'nullable|date',
            'minimum_stock' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
                       ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        abort_unless(auth()->user()?->can('products-delete'), 403);

        $product->delete();

        return redirect()->route('products.index')
                       ->with('success', 'Product deleted successfully.');
    }

    /**
     * Search products for dropdown (API endpoint)
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q'));

        if ($query === '') {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('product_code', 'like', "%{$query}%")
                  ->orWhere('medicine_name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%");
            })
            ->orderBy('medicine_name')
            ->limit(20)
            ->get(['id', 'product_code', 'medicine_name', 'generic_name', 'unit']);

        $products->transform(function (Product $product) {
            $product->text = $product->product_code . ' - ' . $product->medicine_name;

            if (! empty($product->generic_name)) {
                $product->text .= ' (' . $product->generic_name . ')';
            }

            return $product;
        });

        return response()->json($products);
    }
}
