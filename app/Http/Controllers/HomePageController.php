<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\PaluwaganPackage;
use App\Models\Rating;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ProductLoader;

class HomePageController extends Controller
{
    public function index(Request $request)
    {
        // Load DB categories
        $categories = ProductType::orderBy('producttype')->get();

        // Remove Paluwagan if it exists in DB to avoid duplicates
        $categories = $categories->reject(function ($c) {
            return strtolower($c->productType) === 'paluwagan';
        });

        // Add virtual Paluwagan category
        $categories->push((object)[
            'productTypeID' => 'paluwagan',
            'productType'   => 'Paluwagan'
        ]);

        // Define icons for categories
        $categoryIcons = [
            'all' => 'fas fa-th-large',
            'paluwagan' => 'fas fa-hand-holding-usd',
            'cake' => 'fas fa-birthday-cake',
            'cupcake' => 'fas fa-cookie-bite',
            'food package' => 'fas fa-box-open',
            'food tray' => 'fas fa-utensils',
        ];

        // Load normal products
        $allProducts = ProductLoader::loadAllProducts();

        // Remove Paluwagan from normal products
        $allProducts = $allProducts->reject(function ($p) {
            return strtolower($p['productType']) === 'paluwagan';
        });

        $paluwagan = PaluwaganPackage::all()->map(function ($p) {
            return [
                'id' => $p->packageID,
                'name' => $p->packageName,
                'productType' => 'paluwagan',
                'productTypeID' => 'paluwagan',

                // RELATIVE PATH ONLY
                'imageURL' => $p->image
                ? $p->image
                : 'default-paluwagan.jpg',

                'description' => $p->description,
                'descriptionList' => explode('|', $p->description ?? ''),

                'servings' => [
                    [
                        'price' => $p->totalAmount,
                        'size'  => $p->durationMonths . ' Months'
                    ]
                ],

                // keep compatibility with your blade
                'price' => $p->totalAmount
            ];
        });

        // Merge normal products with Paluwagan
        $allProducts = $allProducts->merge($paluwagan);

        // Category filter logic
        $type = $request->filled('type') ? strtolower(trim($request->type)) : null;

        if ($type === 'paluwagan') {
            $featuredProducts = $allProducts->filter(function ($p) {
                return strtolower($p['productType']) === 'paluwagan';
            })->values();

        } elseif ($type && is_numeric($type)) {

            $featuredProducts = $allProducts
                ->where('productTypeID', intval($type))
                ->values();

        } elseif ($type) {

            // Invalid string filter
            $featuredProducts = collect();

        } else {

            // Default: first 8 non-paluwagan products
            $featuredProducts = $allProducts
                ->reject(function ($p) {
                    return strtolower($p['productType']) === 'paluwagan';
                })
                ->take(8)
                ->values();
        }



        // $testimonials = Rating::whereNotNull('comment')
        // ->latest()
        // ->take(6)
        // ->get();

$testimonials = Rating::with([
            'order.customer',
            'order.orderItems.product.productType'  // changed from category
        ])
        ->when(request('rating'), fn($q, $rating) => $q->where('rating', $rating))
        ->when(request('category'), fn($q, $category) => 
            $q->whereHas('order.orderItems.product.productType', fn($q) =>   // changed
                $q->where('productTypeID', $category)))                       // changed
        ->when(request('product'), fn($q, $product) => 
            $q->whereHas('order.orderItems.product', fn($q) => 
                $q->where('product.productID', $product)))
        ->latest()
        ->get(); 

        $productTypes = ProductType::orderBy('productType')->get();
        $products = Product::orderBy('name')->get();


        return view('user.HomePage', compact(
            'categories',
            'featuredProducts',
            'categoryIcons',
            'testimonials',
            'productTypes',  // changed
            'products'
        ));
    }

     public function filterTestimonials(Request $request)
    {
        $testimonials = Rating::with([
            'order.customer',
            'order.orderItems.product.productType'
        ])
        ->when($request->rating, fn($q, $rating) => $q->where('rating', $rating))
        ->when($request->category, fn($q, $category) =>
            $q->whereHas('order.orderItems.product.productType', fn($q) =>
                $q->where('productTypeID', $category)))
        ->when($request->product, fn($q, $product) =>
            $q->whereHas('order.orderItems.product', fn($q) =>
                $q->where('product.productID', $product)))
        ->latest()
        ->get();

        return view('partials.homepage.testimonial-cards', compact('testimonials'));
    }

}