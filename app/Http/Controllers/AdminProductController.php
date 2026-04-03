<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends AdminBaseController
{
    public function index()
    {   
        parent::__construct();
        // 🔒 Role-based access
        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 6)) {
            abort(403, 'Unauthorized');
        }


        $products = Product::with('productType','servings')->orderBy('name')->get();
        $types = ProductType::all();
        return view('admin.Product', compact('products','types'));
    }

    public function ajaxFetch(Request $request)
    {
        $query = Product::with('type','servings');

        if($request->search){
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        if($request->category){
            $query->where('productTypeID', $request->category);
        }

        $products = $query->orderBy('name')->get();
        $html = view('admin.products.product-table', compact('products'))->render();
        return response()->json(['html'=>$html]);
    }

    public function modalEdit($id)
    {
        // Preload servings and type for edit modal
        $product = Product::with('servings','type')->findOrFail($id);
        return response()->json(['product'=>$product]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'=>'required|string|max:255',
            'productTypeID'=>'required|integer|exists:producttype,productTypeID',
            'description'=>'nullable|string',
            'isAvailable'=>'required|boolean',
            'promo'=>'nullable|numeric|min:0|max:100',
            'imageURL'=>'required|image|max:2048'
        ]);

        if($request->hasFile('imageURL')){
            $file = $request->file('imageURL');
            $filename = Str::random(10).'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validated['imageURL'] = $filename;
        }

        $product = Product::create($validated); 

        if($request->ajax()){
            $product->load('type','servings');
            $rowHTML = view('admin.products.product-row', compact('product'))->render();
            return response()->json(['success'=>true,'rowHTML'=>$rowHTML]);
        }

        return redirect()->back()->with('success','Product added!');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name'=>'required|string|max:255',
            'productTypeID'=>'required|integer',
            'description'=>'nullable|string',
            'isAvailable'=>'required|boolean',
            'promo'=>'nullable|numeric|min:0|max:100',
            'imageURL'=>'nullable|image|max:2048'
        ]);

        if($request->hasFile('imageURL')){
            $file = $request->file('imageURL');
            $filename = Str::random(10).'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['imageURL'] = $filename;
        }

        $product->update($data);

        if($request->ajax()){
            $product->load('type','servings');
            $rowHTML = view('admin.products.product-row', compact('product'))->render();
            return response()->json([
                'success'=>true,
                'rowHTML'=>$rowHTML,
                'productID'=>$product->productID
            ]);
        }

        return redirect()->back()->with('success','Product updated!');
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        if($request->ajax()){
            return response()->json(['success'=>true]);
        }

        return redirect()->back()->with('success','Product deleted!');
    }
}