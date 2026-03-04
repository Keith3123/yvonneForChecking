<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ingredient;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Orders
        $totalRevenue = Order::where('status', 'completed')->sum('totalAmount') ?? 0;
        $completedOrders = Order::where('status', 'completed')->count() ?? 0;
        $pendingOrders = Order::where('status', 'pending')->count() ?? 0;

        // Paluwagan totals
        $activePaluwagan = DB::table('paluwaganentry')
            ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
            ->where('paluwaganentry.status', 'ACTIVE')
            ->sum('paluwaganpackage.totalAmount');

        $collected = DB::table('paluwaganentry')
            ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
            ->where('paluwaganentry.status', 'COLLECTED')
            ->sum('paluwaganpackage.totalAmount');

        // Inventory counts
        $totalProducts = Ingredient::count();
        $lowStock = Ingredient::where('currentStock', '>', 0)
                              ->whereColumn('currentStock', '<=', 'minStockLevel')
                              ->count();

        // Total users
        $totalCustomers = Customer::count();

        // Recent orders - latest 5, eager load customer info
        $recentOrders = Order::with('customer')
            ->orderBy('orderDate', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'completedOrders',
            'pendingOrders',
            'activePaluwagan',
            'collected',
            'totalProducts',
            'lowStock',
            'totalCustomers',
            'recentOrders'
        ));
    }
}