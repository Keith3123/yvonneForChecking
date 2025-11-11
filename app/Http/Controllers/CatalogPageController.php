<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CatalogPageController extends Controller
{
    public function index()
    {
        // Dummy products grouped by category
        $products = [
            [
                'name' => 'Chocolate Cake',
                'price' => '₱450',
                'image' => '/images/choco-cake.jpg',
                'description' => 'Rich and moist chocolate cake with ganache.',
                'category' => 'cake'
            ],
            [
                'name' => 'Vanilla Cupcake',
                'price' => '₱150',
                'image' => '/images/vanilla-cupcake.jpg',
                'description' => 'Soft cupcake topped with buttercream frosting.',
                'category' => 'cupcake'
            ],
            [
                'name' => 'Blueberry Cheesecake',
                'price' => '₱320',
                'image' => '/images/blueberry.jpg',
                'description' => 'Creamy cheesecake with blueberry topping.',
                'category' => 'cake'
            ],
            [
                'name' => 'Big Package',
                'price' => '₱350',
                'image' => '/images/food-package.jpg',
                'description' => 'Good for 6–8 pax with rice and sides.',
                'category' => 'foodpackage'
            ],
            [
                'name' => 'Party Tray',
                'price' => '₱600',
                'image' => '/images/food-tray.jpg',
                'description' => 'Great for celebrations and events.',
                'category' => 'foodtrays'
            ],
        ];

        // ✅ Define categories (to show on sidebar)
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
