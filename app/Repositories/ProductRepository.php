<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllWithRelations()
    {
        return Product::with(['productType', 'servings'])->orderBy('productID','desc')->get();
    }

    public function find($id)
    {
        return Product::with('servings')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        return $product->delete();
    }
}