<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with('type')->orderBy('name')->get();
        $types    = ProductType::all();

        $stats = [
            'total'      => Product::count(),
            'cakes'      => Product::where('productTypeID', 1)->count(),
            'cupcakes'   => Product::where('productTypeID', 2)->count(),
            'food_items' => Product::where('productTypeID', 3)->count(),
            'paluwagan'  => Product::where('productTypeID', 4)->count(),
        ];

        return view('admin.Product', compact('products', 'types', 'stats'));
    }

    public function ajaxFetch(Request $request)
    {
        $search   = $request->search;
        $category = $request->category;

        $query = Product::with('type');

        if ($search !== null && $search !== '') {
            $query->where('name', 'LIKE', "%$search%");
        }

        if ($category !== null && $category !== '') {
            $query->where('productTypeID', $category);
        }

        $products = $query->orderBy('name')->get();

        $html = view('admin.products.product-table', compact('products'))->render();

        return response()->json(['html' => $html]);
    }

    public function modalEdit($id)
    {
        $product = Product::findOrFail($id);
        return response()->json(['product' => $product]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'productTypeID' => 'required|integer',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:1',
            'stock'         => 'required|integer|min:0',
            'isAvailable'   => 'required|boolean',
            'imageURL'      => 'required|image',
        ]);

        if ($request->hasFile('imageURL')) {
            $data['imageURL'] = $request->file('imageURL')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products')
            ->with('success', 'Product created successfully!');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'productTypeID' => 'required|integer',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:1',
            'stock'         => 'required|integer|min:0',
            'isAvailable'   => 'required|boolean',
            'imageURL'      => 'nullable|image',
        ]);

        if ($request->hasFile('imageURL')) {
            $data['imageURL'] = $request->file('imageURL')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        Product::destroy($id);

        return redirect()->route('admin.products')
            ->with('success', 'Product deleted successfully!');
    }
}
