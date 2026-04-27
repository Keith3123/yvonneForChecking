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
    PaluwaganPageController,
    AdminDashboardController,
    AdminOrdersController,
    AdminSalesReportController,
    AdminUsersController,
    AdminPaluwaganController,
    AdminInventoryController,
    AdminProductController,
    MyRatingPageController,
    PaymongoWebhookController
};

// ------------------- USER PAGES -------------------
Route::get('/cart/sidebar', fn () =>
    view('partials.cart-sidebar')
);

Route::get('/', [HomePageController::class, 'index'])->name('home');
Route::get('/filter-testimonials', [HomePageController::class, 'filterTestimonials']);

Route::get('/login', [LoginPageController::class, 'index'])->name('login');
Route::post('/login', [LoginPageController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginPageController::class, 'logout'])->name('logout');

//My Rating Page
Route::get('/my-ratings', [MyRatingPageController::class, 'index'])->name('my.ratings');

Route::get('/register', [RegisterPageController::class, 'show'])->name('register');
Route::post('/register', [RegisterPageController::class, 'store'])->name('register.store');

Route::post('/check-username', [RegisterPageController::class, 'checkUsername']);
Route::post('/check-email', [RegisterPageController::class, 'checkEmail']);


Route::get('/catalog', [CatalogPageController::class, 'index'])->name('catalog');

Route::get('/cart', [CartPageController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartPageController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartPageController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartPageController::class, 'clear'])->name('cart.clear');
Route::post('/cart/update', [CartPageController::class, 'update'])->name('cart.update');

Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout');
Route::post('/profile/save-address', [ProfilePageController::class, 'saveAddress'])->name('profile.saveAddress');
Route::get('/checkout/addresses', [CheckoutPageController::class, 'getSavedAddresses'])->name('checkout.addresses');
Route::post('/checkout/addresses/save', [CheckoutPageController::class, 'saveAddress'])->name('checkout.addresses.save');
Route::post('/checkout/place-order', [CheckoutPageController::class, 'placeOrder'])->name('checkout.placeOrder');


Route::get('/orders', [OrdersPageController::class, 'index'])->name('orders.index');
// Route to view receipt
Route::get('/orders/{orderID}/receipt', [OrdersPageController::class, 'viewReceipt'])->name('orders.receipt');
// Route to cancel an order
Route::post('/orders/{orderID}/cancel', [OrdersPageController::class, 'cancelOrder']);
// Route to submit a rating for an order
Route::post('/rate-order', [OrdersPageController::class, 'rate'])->name('rate.order');
// Route to export receipt as PDF
Route::get('/orders/{orderID}/receipt/pdf', [OrdersPageController::class, 'exportReceiptPDF'])->name('orders.receipt.pdf');


// PAYMONGO GCASH FLOW - CHECKOUT PAGE
Route::post('/checkout/paymongo', [CheckoutPageController::class, 'payWithGcash'])->name('checkout.paymongo');
Route::post('/checkout/pay-gcash', [CheckoutPageController::class, 'payWithGcash'])->name('checkout.pay');


Route::get('/checkout/success', [CheckoutPageController::class, 'paymentSuccess'])->name('checkout.payment.success');
Route::get('/checkout/failed', [CheckoutPageController::class, 'paymentFailed'])->name('checkout.payment.failed');


Route::post('/paymongo/webhook', [PaymongoWebhookController::class, 'handle']);


//Profile Page
Route::get('/profile', [ProfilePageController::class, 'index'])->name('profile');
Route::post('/profile/update', [ProfilePageController::class, 'update'])->name('profile.update');
Route::post('/password/update', [ProfilePageController::class, 'updatePassword'])->name('profile.password.update');

// ------------------- PALUWAGAN -------------------
Route::get('/paluwagan', [PaluwaganPageController::class, 'index'])->name('paluwagan'); 
Route::post('/paluwagan/join', [PaluwaganPageController::class, 'join'])->name('paluwagan.join');
Route::get('/paluwagan/schedule/{entryID}', [PaluwaganPageController::class, 'viewSchedule'])->name('paluwagan.schedule');
Route::get('/user/paluwagan/available-months/{packageID}', [PaluwaganPageController::class, 'availableMonths'])->name('user.paluwagan.available-months');
Route::post('/paluwagan/cancel/{id}', [PaluwaganPageController::class, 'cancel']);

// PALUWAGAN GCASH
Route::post('/paluwagan/pay-gcash', [PaluwaganPageController::class, 'payWithGcash'])->name('paluwagan.pay.gcash');

// ------------------- ADMIN PAGES -------------------
Route::prefix('admin')->group(function() {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Product Management Routes
    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products');
    Route::get('/products/ajax/fetch', [AdminProductController::class, 'ajaxFetch']);
    Route::post('/products/store', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');    
    Route::delete('/products/{id}/delete', [AdminProductController::class, 'destroy'])->name('admin.products.delete');
    Route::get('/products/modal/edit/{id}', [AdminProductController::class, 'modalEdit']);


    // Orders Management
    Route::get('/orders', [AdminOrdersController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{orderID}/view', [AdminOrdersController::class, 'viewOrder']);
    Route::post('/orders/{orderID}/update-status', [AdminOrdersController::class, 'updateStatus']);

    // Sales Report
    Route::get('/salesreport', [AdminSalesReportController::class, 'index'])->name('admin.salesreport');

    // User Management
    Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
    Route::get('/users/{id}', [AdminUsersController::class, 'show'])->name('admin.users.show');
    Route::post('/users/create-admin', [AdminUsersController::class, 'storeAdmin'])->name('admin.users.storeAdmin');
    Route::patch('/users/toggle-status/{userID}', [AdminUsersController::class, 'toggleStatus'])->name('admin.users.toggleStatus');


    // Paluwagan Management
    Route::get('/paluwagan', [AdminPaluwaganController::class, 'index'])->name('admin.paluwagan');
    Route::post('/paluwagan/package/create', [AdminPaluwaganController::class, 'createPackage']);
    Route::delete('/paluwagan/package/{id}/delete', [AdminPaluwaganController::class,'destroy']);
    Route::put('/paluwagan/package/{id}', [AdminPaluwaganController::class,'updatePackage']);
    Route::post('/paluwagan/month/toggle', [AdminPaluwaganController::class,'toggleMonth']);
    Route::post('/paluwagan/entry/{id}/complete', [AdminPaluwaganController::class, 'complete'])->name('admin.paluwagan.complete');
    Route::post('/paluwagan/entry/{entryID}/reassign', [AdminPaluwaganController::class, 'reassign']);

    Route::get('/paluwagan/entry/{id}/payments', [AdminPaluwaganController::class, 'getPayments']);
    Route::get('/paluwagan/customers/search', [AdminPaluwaganController::class, 'searchCustomers']);

    // Inventory Routes
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('admin.inventory');

    // POST route for adding ingredients (fetch/JSON)
    Route::post('/inventory/store', [AdminInventoryController::class, 'store'])->name('inventory.store');

    // PUT route for editing ingredients (fetch/JSON)
    Route::put('/inventory/{id}', [AdminInventoryController::class, 'update'])->name('inventory.update');

    Route::post('/admin/logout', [LoginPageController::class, 'logout'])->name('admin.logout');
});
