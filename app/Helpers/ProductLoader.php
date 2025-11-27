<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ProductLoader
{
    public static function loadAllProducts()
    {
        // Fetch regular products
        $products = DB::table('product')
            ->leftJoin('producttype', 'product.productTypeID', '=', 'producttype.productTypeID')
            ->leftJoin('serving', 'product.productID', '=', 'serving.productID')
            ->select(
                'product.productID',
                'product.productTypeID',
                'product.name',
                'product.description',
                'product.imageURL',
                'producttype.productType',
                'serving.size',
                'serving.price'
            )
            ->get()
            ->groupBy('productID')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'id' => $first->productID,
                    'productTypeID' => $first->productTypeID,
                    'name' => $first->name,
                    'description' => $first->description,
                    'descriptionList' => $first->description
                        ? array_filter(array_map('trim', explode("\n", $first->description)))
                        : [],
                    'imageURL' => $first->imageURL,
                    'productType' => strtolower(trim($first->productType)), // ensure lowercase & no spaces
                    'servings' => $group->map(fn($g) => [
                        'size' => $g->size,
                        'price' => $g->price
                    ])->values()->toArray(), // convert to array
                ];
            })
            ->values();

        // Fetch paluwagan packages
        $paluwagan = DB::table('paluwaganpackage')
            ->select(
                'packageID as id',
                'packageName as name',
                'description',
                'totalAmount',
                'durationMonths',
                'image',
                DB::raw("'paluwagan' as productType")
            )
            ->get()
            ->map(function ($pkg) {
                return [
                    'id' => $pkg->id,
                    'packageID' => $pkg->id, 
                    'name' => $pkg->name,
                    'description' => $pkg->description ?? '',
                    'descriptionList' => $pkg->description
                        ? array_filter(array_map('trim', explode("\n", $pkg->description)))
                        : [],
                    'imageURL' => 'images/' . $pkg->image,
                    'productType' => 'paluwagan',
                    'servings' => [
                        [
                            'size' => (int)$pkg->durationMonths,
                            'price' => (float)$pkg->totalAmount,
                        ]
                    ],
                ];
            });

        // Merge regular products + paluwagan packages
        return $products->merge($paluwagan);
    }
}
