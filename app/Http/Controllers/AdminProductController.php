<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    // ------------------------------------------
    // INDEX PAGE
    // ------------------------------------------
    public function index(Request $request)
    {
        $products = Product::with('type')->orderBy('name')->get();
        $types    = ProductType::all();

        // Stats
        $stats = [
            'total'      => Product::count(),
            'cakes'      => Product::where('productTypeID', 1)->count(),
            'cupcakes'   => Product::where('productTypeID', 2)->count(),
            'food_items' => Product::where('productTypeID', 3)->count(),
            'paluwagan'  => Product::where('productTypeID', 4)->count(),
        ];

        return view('admin.products.index', compact('products', 'types', 'stats'));
    }

    // ------------------------------------------
    // AJAX FILTER + SEARCH
    // ------------------------------------------
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

    // ------------------------------------------
    // CREATE PAGE
    // ------------------------------------------
    public function create()
    {
        $types = ProductType::all();
        return view('admin.products.add-product-modal', compact('types'));
    }

    // ------------------------------------------
    // STORE PRODUCT
    // ------------------------------------------
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

    // ------------------------------------------
    // EDIT PAGE
    // ------------------------------------------
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $types   = ProductType::all();

        return view('admin.products.edit-product-modal', compact('product', 'types'));
    }

    // ------------------------------------------
    // UPDATE PRODUCT
    // ------------------------------------------
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

    // ------------------------------------------
    // DELETE PRODUCT
    // ------------------------------------------
    public function destroy($id)
    {
        Product::destroy($id);

        return redirect()->route('admin.products')
            ->with('success', 'Product deleted successfully!');
    }
}

