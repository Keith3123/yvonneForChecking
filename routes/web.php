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
        AdminInventoryController
    };

    // ------------------- USER PAGES -------------------
    Route::get('/', [HomePageController::class, 'index'])->name('home');

    Route::get('/register', [RegisterPageController::class, 'show'])->name('register');
    Route::post('/register', [RegisterPageController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginPageController::class, 'index'])->name('login');
    Route::post('/login', [LoginPageController::class, 'store'])->name('login.store');
    Route::post('/logout', [LoginPageController::class, 'logout'])->name('logout');

    Route::post('/check-username', [RegisterPageController::class, 'checkUsername']);

    Route::get('/catalog', [CatalogPageController::class, 'index'])->name('catalog');

    Route::get('/cart', [CartPageController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartPageController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartPageController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartPageController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/update', [CartPageController::class, 'update'])->name('cart.update');

    Route::get('/checkout', [CheckoutPageController::class, 'index'])->name('checkout');
    Route::post('/checkout/place-order', [CheckoutPageController::class, 'placeOrder'])->name('checkout.placeOrder');

    Route::get('/orders', [OrdersPageController::class, 'index'])->name('orders.index');

   Route::get('/profile', [ProfilePageController::class, 'index'])->name('profile');
Route::post('/profile/update', [ProfilePageController::class, 'update'])->name('profile.update');
Route::post('/password/update', [ProfilePageController::class, 'updatePassword'])
     ->name('password.update');

    // ------------------- PALUWAGAN -------------------
    Route::get('/paluwagan', [PaluwaganPageController::class, 'index'])->name('paluwagan'); 
    Route::post('/paluwagan/join', [PaluwaganPageController::class, 'join'])->name('paluwagan.join');
    Route::get('/paluwagan/schedule/{entryID}', [PaluwaganPageController::class, 'viewSchedule'])->name('paluwagan.schedule');


    // ------------------- ADMIN PAGES -------------------
    Route::prefix('admin')->group(function() {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::patch('/admin/users/toggle-status/{userID}', [AdminUsersController::class, 'toggleStatus'])->name('admin.users.toggleStatus');



        Route::get('/orders', [AdminOrdersController::class, 'index'])->name('admin.orders');
        Route::get('/salesreport', [AdminSalesReportController::class, 'index'])->name('admin.salesreport');
        Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users');
        Route::get('/users/{id}', [AdminUsersController::class, 'show'])->name('admin.users.show');
        Route::post('/users/create-admin', [AdminUsersController::class, 'storeAdmin'])->name('admin.users.storeAdmin');
        Route::get('/paluwagan', [AdminPaluwaganController::class, 'index'])->name('admin.paluwagan');

        // Inventory Routes
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('admin.inventory');

        // POST route for adding ingredients (fetch/JSON)
        Route::post('/inventory/store', [AdminInventoryController::class, 'store'])->name('inventory.store');

        // PUT route for editing ingredients (fetch/JSON)
        Route::put('/inventory/{id}', [AdminInventoryController::class, 'update'])->name('inventory.update');
    });
