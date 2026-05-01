<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ingredient;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends AdminBaseController
{
    public function index()
    {
        parent::__construct();

        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 1)) {
            abort(403, 'Unauthorized');
        }

        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // ===========================
        // MAIN STATS (All Time)
        // ===========================
        // ✅ FIXED: 'Done' not 'completed' — matches your DB enum
        $totalRevenue    = (float) Order::where('status', 'Done')->sum('totalAmount');
        $completedOrders = Order::where('status', 'Done')->count();
        $pendingOrders   = Order::whereIn('status', ['Pending', 'Confirmed', 'Preparing'])->count();
        $totalOrders     = Order::count();

        // ===========================
        // TODAY'S STATS
        // ===========================
        $todayRevenue = (float) Order::whereDate('orderDate', $today)
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $todayOrders = Order::whereDate('orderDate', $today)->count();

        $yesterdayRevenue = (float) Order::whereDate('orderDate', $yesterday)
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $yesterdayOrders = Order::whereDate('orderDate', $yesterday)->count();

        // Today growth vs yesterday
        $todayRevenueGrowth = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : ($todayRevenue > 0 ? 100 : 0);

        $todayOrderGrowth = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
            : ($todayOrders > 0 ? 100 : 0);

        // ===========================
        // THIS WEEK STATS
        // ===========================
        $weekRevenue = (float) Order::whereBetween('orderDate', [$weekStart, now()])
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $weekOrders = Order::whereBetween('orderDate', [$weekStart, now()])->count();

        // ===========================
        // THIS MONTH STATS
        // ===========================
        $monthRevenue = (float) Order::whereBetween('orderDate', [$monthStart, now()])
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $monthOrders = Order::whereBetween('orderDate', [$monthStart, now()])->count();

        // ===========================
        // WEEKLY CHART (Last 7 days)
        // ===========================
        $chartLabels  = [];
        $chartRevenue = [];
        $chartOrders  = [];

        for ($i = 6; $i >= 0; $i--) {
            $day      = Carbon::today()->subDays($i);
            $dayStart = (clone $day)->startOfDay();
            $dayEnd   = (clone $day)->endOfDay();

            $chartLabels[]  = $day->format('D, M d');
            $chartRevenue[] = (float) Order::whereBetween('orderDate', [$dayStart, $dayEnd])
                ->where('status', '!=', 'Cancelled')
                ->sum('totalAmount');
            $chartOrders[] = Order::whereBetween('orderDate', [$dayStart, $dayEnd])->count();
        }

        // ===========================
        // ORDER STATUS BREAKDOWN
        // ===========================
        $statusBreakdown = Order::select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        // ===========================
        // TOP 5 PRODUCTS (This Month)
        // ===========================
        $topProducts = DB::table('orderitem')
            ->join('order', 'orderitem.orderID', '=', 'order.orderID')
            ->join('product', 'orderitem.productID', '=', 'product.productID')
            ->select(
                'product.name',
                'product.imageURL',
                DB::raw('SUM(orderitem.qty) as total_units'),
                DB::raw('SUM(orderitem.subtotal) as total_revenue')
            )
            ->whereBetween('order.orderDate', [$monthStart, now()])
            ->where('order.status', '!=', 'Cancelled')
            ->groupBy('product.productID', 'product.name', 'product.imageURL')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // ===========================
        // PALUWAGAN
        // ===========================
        $activePaluwagan = (float) DB::table('paluwaganentry')
            ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
            ->where('paluwaganentry.status', 'ACTIVE')
            ->sum('paluwaganpackage.totalAmount');

        $collected = (float) DB::table('paluwaganentry')
            ->join('paluwaganpackage', 'paluwaganentry.packageID', '=', 'paluwaganpackage.packageID')
            ->where('paluwaganentry.status', 'COLLECTED')
            ->sum('paluwaganpackage.totalAmount');

        $activePaluwaganCount = DB::table('paluwaganentry')
            ->where('status', 'ACTIVE')
            ->count();

        // ===========================
        // INVENTORY
        // ===========================
        $totalIngredients = Ingredient::count();
        $lowStock = Ingredient::where('currentStock', '>', 0)
            ->whereColumn('currentStock', '<=', 'minStockLevel')
            ->count();
        $outOfStock = Ingredient::where('currentStock', '<=', 0)->count();

        $lowStockItems = Ingredient::where('currentStock', '>', 0)
            ->whereColumn('currentStock', '<=', 'minStockLevel')
            ->orderBy('currentStock', 'asc')
            ->limit(5)
            ->get();

        // ===========================
        // USERS
        // ===========================
        $totalCustomers = Customer::count();
        $newCustomersThisMonth = Customer::where('created_at', '>=', $monthStart)->count();

        // ===========================
        // RECENT ORDERS (Latest 8)
        // ===========================
        $recentOrders = Order::with('customer')
            ->orderBy('orderDate', 'desc')
            ->limit(8)
            ->get();

        // ===========================
        // PAYMENT STATS (Today)
        // ===========================
        $todayPayments = DB::table('payment')
            ->join('order', 'payment.orderID', '=', 'order.orderID')
            ->where('payment.contextType', 'order')
            ->whereDate('order.orderDate', $today)
            ->select(
                'payment.method',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('SUM(payment.amount) as total')
            )
            ->groupBy('payment.method')
            ->get();

        $todayGcash = (float) $todayPayments->where('method', 'GCASH')->sum('total');
        $todayCod   = (float) $todayPayments->where('method', 'COD')->sum('total');

        return view('admin.dashboard', compact(
            'totalRevenue', 'completedOrders', 'pendingOrders', 'totalOrders',
            'todayRevenue', 'todayOrders', 'todayRevenueGrowth', 'todayOrderGrowth',
            'weekRevenue', 'weekOrders',
            'monthRevenue', 'monthOrders',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'statusBreakdown', 'topProducts',
            'activePaluwagan', 'collected', 'activePaluwaganCount',
            'totalIngredients', 'lowStock', 'outOfStock', 'lowStockItems',
            'totalCustomers', 'newCustomersThisMonth',
            'recentOrders',
            'todayGcash', 'todayCod'
        ));
    }
}