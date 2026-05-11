<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Serving;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    protected $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function createProduct($request)
{
    $filename = null;

    if ($request->hasFile('imageURL')) {
        $file = $request->file('imageURL');
        $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $dest = public_path('images/products');
        if (!file_exists($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $filename);
    }

    $product = $this->repo->create([
        'name'          => $request->name,
        'productTypeID' => $request->productTypeID,
        'description'   => $request->description,
        'isAvailable'   => $request->isAvailable,
        'promo'         => $request->promo,
        'imageURL'      => $filename,
    ]);

    $this->saveServings($product->productID, $request);

    return $product;
}

public function updateProduct($request, $id)
{
    $product = $this->repo->find($id);

    if ($request->hasFile('imageURL')) {
        // Delete old image
        if ($product->imageURL) {
            $oldPath = public_path('images/products/' . $product->imageURL);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $file = $request->file('imageURL');
        $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $dest = public_path('images/products');
        if (!file_exists($dest)) mkdir($dest, 0755, true);
        $file->move($dest, $filename);
        
        $product->imageURL = $filename;
        $product->save();
    }

    $this->repo->update($product, [
        'name'          => $request->name,
        'productTypeID' => $request->productTypeID,
        'description'   => $request->description,
        'isAvailable'   => $request->isAvailable,
        'promo'         => $request->promo,
    ]);

    Serving::where('productID', $id)->delete();
    $this->saveServings($id, $request);

    return $product;
}

public function deleteProduct($id)
{
    $product = $this->repo->find($id);

    if ($product->imageURL) {
        $oldPath = public_path('images/products/' . $product->imageURL);
        if (file_exists($oldPath)) unlink($oldPath);
    }

    $this->repo->delete($product);
}
}