<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomePageController,
    RegisterPageController,
    LoginPageController,
    CatalogPageController,
    ProductDetailsPageController,
    CartPageController,
    CheckoutPageController,
    OrdersPageController,
    ProfilePageController,
    PaluwaganPageController
};

// ------------------- USER PAGES -------------------

// Home
Route::get('/', [HomePageController::class, 'index'])->name('home');

// Register
Route::get('/register', [RegisterPageController::class, 'show'])->name('register');
Route::post('/register', [RegisterPageController::class, 'store'])->name('register.store');

// Login
Route::get('/login', [LoginPageController::class, 'index'])->name('login');
Route::post('/login', [LoginPageController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginPageController::class, 'logout'])->name('logout');

// Catalog - requires login
Route::get('/catalog', [CatalogPageController::class, 'index'])->name('catalog');

// Cart routes
Route::get('/cart', [CartPageController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartPageController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartPageController::class, 'remove'])->name('cart.remove'); // optional
Route::post('/cart/clear', [CartPageController::class, 'clear'])->name('cart.clear');


// Checkout
Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout');
Route::post('/checkout/place-order', [CheckoutPageController::class, 'placeOrder'])->name('checkout.placeOrder');


// Other user routes
Route::get('/product/{id}', [ProductDetailsPageController::class, 'show'])->name('product.details');

// Orders
Route::resource('orders', OrdersPageController::class);

// Profile
Route::get('/profile', function () {
    return view('user.ProfilePage');
})->name('profile');


// Paluwagan
Route::get('/paluwagan', [PaluwaganPageController::class, 'index'])->name('paluwagan');

 




