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
            $filename = preg_replace('/[^A-Za-z0-9\.\-_]/','_', $file->getClientOriginalName());
            $file->storeAs('public/products', $filename);
        }

        $product = $this->repo->create([
            'name'=>$request->name,
            'productTypeID'=>$request->productTypeID,
            'description'=>$request->description,
            'isAvailable'=>$request->isAvailable,
            'promo'=>$request->promo,
            'imageURL'=>$filename
        ]);

        $this->saveServings($product->productID, $request);

        return $product;
    }

    public function updateProduct($request, $id)
    {
        $product = $this->repo->find($id);

        if ($request->hasFile('imageURL')) {
            if ($product->imageURL) Storage::delete('public/products/'.$product->imageURL);

            $file = $request->file('imageURL');
            $filename = preg_replace('/[^A-Za-z0-9\.\-_]/','_', $file->getClientOriginalName());
            $file->storeAs('public/products', $filename);
            $product->imageURL = $filename;
        }

        $this->repo->update($product, [
            'name'=>$request->name,
            'productTypeID'=>$request->productTypeID,
            'description'=>$request->description,
            'isAvailable'=>$request->isAvailable,
            'promo'=>$request->promo
        ]);

        // reset servings
        Serving::where('productID', $id)->delete();
        $this->saveServings($id, $request);

        return $product;
    }

    public function deleteProduct($id)
    {
        $product = $this->repo->find($id);

        if ($product->imageURL) Storage::delete('public/products/'.$product->imageURL);

        $this->repo->delete($product);
    }

    private function saveServings($productID, $request)
    {
        foreach(['s','m','l'] as $size){
            $field = "size_$size";
            if ($request->$field) {
                Serving::create([
                    'productID'=>$productID,
                    'size'=>strtoupper($size),
                    'price'=>$request->$field
                ]);
            }
        }
    }
}