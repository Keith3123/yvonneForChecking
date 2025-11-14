<?php

namespace App\Http\Controllers;

class CatalogPageController extends Controller
{
    public function index()
    {
        // Redirect if not logged in
        if (!session('logged_in_user')) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $products = include(resource_path('data/products.php'));

        // Define categories for sidebar
        $categories = [
            ['name' => 'Paluwagan', 'image' => '/images/paluwagan.jpg', 'key' => 'paluwagan'],
            ['name' => 'Food Package', 'image' => '/images/food-package.jpg', 'key' => 'foodpackage'],
            ['name' => 'Food Trays', 'image' => '/images/food-tray.jpg', 'key' => 'foodtrays'],
            ['name' => 'Cake', 'image' => '/images/choco-cake.jpg', 'key' => 'cake'],
            ['name' => 'Cup Cake', 'image' => '/images/vanilla-cupcake.jpg', 'key' => 'cupcake'],
        ];

        return view('user.CatalogPage', compact('products', 'categories'));
    }
}
