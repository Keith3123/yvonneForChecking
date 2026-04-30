<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\Serving;
use App\Models\Ingredient;
use App\Models\ListOfIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminProductController extends AdminBaseController
{
    public function index()
    {
        parent::__construct();

        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 6)) {
            abort(403, 'Unauthorized');
        }

        $products = Product::with(['type', 'servings.ingredientsList.ingredient'])
            ->orderBy('name')
            ->get();

        $types = ProductType::all();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('admin.Product', compact('products', 'types', 'ingredients'));
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'name'          => 'required|string|max:255',
            'productTypeID' => 'required|integer|exists:producttype,productTypeID',
            'description'   => 'nullable|string',
            'isAvailable'   => 'required|boolean',
            'promo'         => 'nullable|numeric|min:0|max:100',
            'imageURL'      => 'required|image|max:2048',
        ]);

        DB::beginTransaction();

        $product = Product::create([
            'name'          => $request->name,
            'productTypeID' => $request->productTypeID,
            'description'   => $request->description,
            'isAvailable'   => $request->isAvailable,
            'promo'         => $request->promo,
            'imageURL'      => $this->uploadImage($request),  // ✅ only called once
        ]);

        $this->saveServings($product->productID, $request);

        DB::commit();

        $product->load(['type', 'servings.ingredientsList.ingredient']);
        $rowHTML = view('admin.products.product-row', compact('product'))->render();
        return response()->json(['success' => true, 'rowHTML' => $rowHTML]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Product store error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function update(Request $request, $id)
{
    try {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'productTypeID' => 'required|integer|exists:producttype,productTypeID',
            'description'   => 'nullable|string',
            'isAvailable'   => 'required|boolean',
            'promo'         => 'nullable|numeric|min:0|max:100',
            'imageURL'      => 'nullable|image|max:2048',
        ]);

        $data = [
            'name'          => $request->name,
            'productTypeID' => $request->productTypeID,
            'description'   => $request->description,
            'isAvailable'   => $request->isAvailable,
            'promo'         => $request->promo,
        ];

        // ✅ Only upload new image if one was actually sent
        if ($request->hasFile('imageURL')) {
            // Delete old image
            if ($product->imageURL) {
                $oldPath = public_path('images/products/' . $product->imageURL);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['imageURL'] = $this->uploadImage($request);
        }
        // If no new image uploaded, imageURL is NOT in $data
        // so $product->update($data) keeps the existing imageURL untouched ✅

        DB::beginTransaction();

        $product->update($data);

        $oldServingIDs = Serving::where('productID', $id)->pluck('servingID');
        ListOfIngredient::whereIn('servingID', $oldServingIDs)->delete();
        Serving::where('productID', $id)->delete();

        $this->saveServings($id, $request);

        DB::commit();

        $product->load(['type', 'servings.ingredientsList.ingredient']);
        $rowHTML = view('admin.products.product-row', compact('product'))->render();
        return response()->json([
            'success'   => true,
            'rowHTML'   => $rowHTML,
            'productID' => $product->productID,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Product update error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    public function toggleAvailability($id)
{
    try {
        $product = Product::findOrFail($id);
        $product->isAvailable = !$product->isAvailable;
        $product->save();

        return response()->json([
            'success'     => true,
            'isAvailable' => $product->isAvailable,
        ]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

private function uploadImage(Request $request): string
{
    $file     = $request->file('imageURL');
    $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
    
    // Save to public/images/products/ — works with ngrok, no symlink needed
    $dest = public_path('images/products');
    if (!file_exists($dest)) mkdir($dest, 0755, true);
    $file->move($dest, $filename);
    
    return $filename;
}

    public function modalEdit($id)
{
    $product = Product::with([
        'type',
        'servings.ingredientsList.ingredient'
    ])->findOrFail($id);

    $servingsData = $product->servings->map(fn($serving) => [
        'servingID'   => $serving->servingID,
        'size'        => $serving->size,
        'servingSize' => (float) $serving->servingSize,
        'unit'        => $serving->unit,
        'price'       => (float) $serving->price,
        'qtyNeeded'   => (float) $serving->qtyNeeded,
        'ingredients' => $serving->ingredientsList->map(fn($item) => [
            'ingredientID' => $item->ingredientID,
            'name'         => $item->ingredient->name ?? 'Unknown',
            'qtyUsed'      => (float) $item->qtyUsed,
        ])->values(),
    ]);

    return response()->json([
        'product'  => $product,
        'servings' => $servingsData,
    ]);
}

    /**
     * Save servings + ingredients from request
     * Expected: servings[0][size], servings[0][ingredients][0][ingredientID], etc.
     */
    private function saveServings($productID, Request $request)
{
    $servingsData = $request->input('servings', []);

    foreach ($servingsData as $servData) {
        if (empty($servData['size']) && empty($servData['price'])) continue;

        $serving = Serving::create([
            'productID'   => $productID,
            'size'        => $servData['size']        ?? 'Standard',
            'servingSize' => $servData['servingSize'] ?? 0,
            'unit'        => $servData['unit']        ?? 'pcs',
            'price'       => $servData['price']       ?? 0,
            'qtyNeeded'   => $servData['qtyNeeded']   ?? 0,
        ]);

        foreach (($servData['ingredients'] ?? []) as $ingData) {
            if (empty($ingData['ingredientID'])) continue;

            ListOfIngredient::create([
                'servingID'    => $serving->servingID,
                'prepID'       => null,  // ✅ null instead of 0
                'ingredientID' => $ingData['ingredientID'],
                'qtyUsed'      => $ingData['qtyUsed'] ?? 0,
            ]);
        }
    }
}
}