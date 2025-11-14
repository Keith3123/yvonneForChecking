<?php

namespace App\Http\Controllers;

class HomePageController extends Controller
{
    public function index()
    {
        // Load all products
        $allProducts = include(resource_path('data/products.php'));

        // Pick first 3 for featured/best sellers
        $featuredProducts = array_slice($allProducts, 0, 3);

        return view('user.HomePage', compact('featuredProducts'));
    }
}
