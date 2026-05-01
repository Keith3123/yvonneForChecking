<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSalesReportController extends AdminBaseController
{
    public function index(Request $request)
    {
        parent::__construct();
        $user = session('admin_user');
        if (!$user || ($user['username'] !== 'masteradmin' && $user['roleID'] != 5)) {
            abort(403, 'Unauthorized');
        }

        // ===========================
        // DETERMINE PERIOD
        // ===========================
        $period    = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $dates    = $this->getDateRange($period, $startDate, $endDate);
        $from     = $dates['from'];
        $to       = $dates['to'];
        $prevFrom = $dates['prev_from'];
        $prevTo   = $dates['prev_to'];

        // ===========================
        // 1. STATS
        // ===========================
        $totalRevenue = (float) DB::table('order')
            ->whereBetween('orderDate', [$from, $to])
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $totalOrders = DB::table('order')
            ->whereBetween('orderDate', [$from, $to])
            ->count();

        $completedOrders = DB::table('order')
            ->whereBetween('orderDate', [$from, $to])
            ->where('status', 'Done')
            ->count();

        $cancelledOrders = DB::table('order')
            ->whereBetween('orderDate', [$from, $to])
            ->where('status', 'Cancelled')
            ->count();

        $pendingOrders = DB::table('order')
            ->whereBetween('orderDate', [$from, $to])
            ->where('status', 'Pending')
            ->count();

        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        // Previous period for growth
        $prevRevenue = (float) DB::table('order')
            ->whereBetween('orderDate', [$prevFrom, $prevTo])
            ->where('status', '!=', 'Cancelled')
            ->sum('totalAmount');

        $prevOrders = DB::table('order')
            ->whereBetween('orderDate', [$prevFrom, $prevTo])
            ->count();

        $revenueGrowth = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($totalRevenue > 0 ? 100 : 0);

        $orderGrowth = $prevOrders > 0
            ? round((($totalOrders - $prevOrders) / $prevOrders) * 100, 1)
            : ($totalOrders > 0 ? 100 : 0);

        // ===========================
        // 2. SALES TREND (Chart)
        // ===========================
        $trendLabels  = [];
        $trendRevenue = [];
        $trendOrders  = [];

        if ($period === 'daily') {
            // Hourly
            for ($h = 0; $h < 24; $h++) {
                $hStart = (clone $from)->addHours($h);
                $hEnd   = (clone $from)->addHours($h + 1);

                $trendLabels[]  = $hStart->format('g A');
                $trendRevenue[] = (float) DB::table('order')
                    ->whereBetween('orderDate', [$hStart, $hEnd])
                    ->where('status', '!=', 'Cancelled')
                    ->sum('totalAmount');
                $trendOrders[] = DB::table('order')
                    ->whereBetween('orderDate', [$hStart, $hEnd])
                    ->count();
            }
        } else {
            // Daily breakdown
            $cursor = clone $from;
            while ($cursor->lte($to)) {
                $dayStart = (clone $cursor)->startOfDay();
                $dayEnd   = (clone $cursor)->endOfDay();

                $trendLabels[]  = $cursor->format($period === 'weekly' ? 'D M d' : 'M d');
                $trendRevenue[] = (float) DB::table('order')
                    ->whereBetween('orderDate', [$dayStart, $dayEnd])
                    ->where('status', '!=', 'Cancelled')
                    ->sum('totalAmount');
                $trendOrders[] = DB::table('order')
                    ->whereBetween('orderDate', [$dayStart, $dayEnd])
                    ->count();

                $cursor->addDay();
            }
        }

        // ===========================
        // 3. TOP SELLING PRODUCTS
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
            ->whereBetween('order.orderDate', [$from, $to])
            ->where('order.status', '!=', 'Cancelled')
            ->groupBy('product.productID', 'product.name', 'product.imageURL')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // ===========================
        // 4. PAYMENT SUMMARY
        // ===========================
        $paymentStats = DB::table('payment')
            ->join('order', 'payment.orderID', '=', 'order.orderID')
            ->select(
                'payment.method',
                'payment.status',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('SUM(payment.amount) as total')
            )
            ->where('payment.contextType', 'order')
            ->whereBetween('order.orderDate', [$from, $to])
            ->groupBy('payment.method', 'payment.status')
            ->get();

        $gcashTotal    = (float) $paymentStats->where('method', 'GCASH')->sum('total');
        $codTotal      = (float) $paymentStats->where('method', 'COD')->sum('total');
        $paidTotal     = (float) $paymentStats->where('status', 'approved')->sum('total');
        $pendingTotal  = (float) $paymentStats->where('status', 'pending')->sum('total');
        $rejectedTotal = (float) $paymentStats->where('status', 'rejected')->sum('total');
        $paidCount     = (int) $paymentStats->where('status', 'approved')->sum('cnt');
        $pendingCount  = (int) $paymentStats->where('status', 'pending')->sum('cnt');
        $rejectedCount = (int) $paymentStats->where('status', 'rejected')->sum('cnt');

        // ===========================
        // 5. ORDER STATUS BREAKDOWN
        // ===========================
        $statusBreakdown = DB::table('order')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->whereBetween('orderDate', [$from, $to])
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();

        // ===========================
        // PASS EVERYTHING TO VIEW
        // ===========================
        return view('admin.salesreport', compact(
            'period', 'from', 'to',
            'totalRevenue', 'totalOrders', 'completedOrders',
            'cancelledOrders', 'pendingOrders', 'avgOrderValue',
            'revenueGrowth', 'orderGrowth',
            'trendLabels', 'trendRevenue', 'trendOrders',
            'topProducts',
            'gcashTotal', 'codTotal',
            'paidTotal', 'pendingTotal', 'rejectedTotal',
            'paidCount', 'pendingCount', 'rejectedCount',
            'statusBreakdown'
        ));
    }

    /**
     * Date range logic
     */
    private function getDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        if ($startDate && $endDate) {
            $from     = Carbon::parse($startDate)->startOfDay();
            $to       = Carbon::parse($endDate)->endOfDay();
            $diff     = $from->diffInDays($to) + 1;
            $prevFrom = (clone $from)->subDays($diff)->startOfDay();
            $prevTo   = (clone $from)->subDay()->endOfDay();

            return ['from' => $from, 'to' => $to, 'prev_from' => $prevFrom, 'prev_to' => $prevTo];
        }

        switch ($period) {
            case 'daily':
                $from     = Carbon::today()->startOfDay();
                $to       = Carbon::today()->endOfDay();
                $prevFrom = Carbon::yesterday()->startOfDay();
                $prevTo   = Carbon::yesterday()->endOfDay();
                break;

            case 'weekly':
                $from     = Carbon::now()->startOfWeek();
                $to       = Carbon::now()->endOfDay();
                $prevFrom = Carbon::now()->subWeek()->startOfWeek();
                $prevTo   = Carbon::now()->subWeek()->endOfWeek();
                break;

            case 'monthly':
            default:
                $from     = Carbon::now()->startOfMonth();
                $to       = Carbon::now()->endOfDay();
                $prevFrom = Carbon::now()->subMonth()->startOfMonth();
                $prevTo   = Carbon::now()->subMonth()->endOfMonth();
                break;
        }

        return ['from' => $from, 'to' => $to, 'prev_from' => $prevFrom, 'prev_to' => $prevTo];
    }

    /**
     * CSV Export
     */
    public function exportCSV(Request $request)
    {
        $period    = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $dates     = $this->getDateRange($period, $startDate, $endDate);

        $orders = Order::with(['orderItems.product', 'customer', 'payment'])
            ->whereBetween('orderDate', [$dates['from'], $dates['to']])
            ->orderBy('orderDate', 'desc')
            ->get();

        $filename = 'sales_report_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($orders) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Order ID', 'Customer', 'Order Date', 'Status',
                'Payment Method', 'Payment Status', 'Total Amount',
                'Delivery Address', 'Items',
            ]);

            foreach ($orders as $order) {
                $customerName = 'N/A';
                if ($order->customer) {
                    $customerName = trim(
                        ($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')
                    );
                }

                $items = $order->orderItems->map(function ($item) {
                    return ($item->product->name ?? 'Unknown') . ' x' . $item->qty;
                })->implode('; ');

                fputcsv($file, [
                    $order->orderID,
                    $customerName,
                    $order->orderDate->format('Y-m-d H:i'),
                    $order->status,
                    $order->payment->method ?? 'COD',
                    $order->paymentStatus,
                    number_format((float) $order->totalAmount, 2),
                    $order->deliveryAddress,
                    $items,
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}