<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ProductLoader
{
    public static function loadAllProducts()
    {
        $products = DB::table('product')
            ->leftJoin('producttype', 'product.productTypeID', '=', 'producttype.productTypeID')
            ->leftJoin('serving', 'product.productID', '=', 'serving.productID')
            ->where('product.isAvailable', 1)
            ->select(
                'product.productID',
                'product.productTypeID',
                'product.name',
                'product.description',
                'product.imageURL',
                'product.promo',
                'producttype.productType',
                'serving.servingID',
                'serving.size',
                'serving.servingSize',
                'serving.unit',
                'serving.price',
                'serving.qtyNeeded'
            )
            ->get()
            ->groupBy('productID')
            ->map(function ($group) {
                $first = $group->first();
                $promo = $first->promo ? (float) $first->promo : 0;

                return [
                    'id'            => $first->productID,
                    'productTypeID' => $first->productTypeID,
                    'name'          => $first->name,
                    'description'   => $first->description ?? '',
                    'imageURL'      => $first->imageURL,
                    'productType'   => strtolower(preg_replace('/[^a-z]/i', '', $first->productType ?? '')),
                    'promo'         => $promo,
                    'servings'      => $group
                        ->filter(fn($g) => $g->servingID !== null)
                        ->map(function ($g) use ($promo) {
                            $originalPrice = (float) $g->price;
                            $discountedPrice = $promo > 0
                                ? round($originalPrice * (1 - $promo / 100), 2)
                                : $originalPrice;

                            return [
                                'servingID'       => $g->servingID,
                                'size'            => $g->size,
                                'servingSize'     => $g->servingSize,
                                'unit'            => $g->unit,
                                'originalPrice'   => $originalPrice,
                                'price'           => $discountedPrice,
                                'qtyNeeded'       => $g->qtyNeeded,
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->values();

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
            ->map(fn($pkg) => [
                'id'          => $pkg->id,
                'name'        => $pkg->name,
                'description' => $pkg->description ?? '',
                'imageURL'    => $pkg->image,
                'productType' => 'paluwagan',
                'promo'       => null,
                'servings'    => [[
                    'size'          => (int) $pkg->durationMonths,
                    'servingSize'   => null,
                    'unit'          => 'months',
                    'originalPrice' => (float) $pkg->totalAmount,
                    'price'         => (float) $pkg->totalAmount,
                    'qtyNeeded'     => null,
                ]],
            ]);

        return $products->merge($paluwagan);
    }
}