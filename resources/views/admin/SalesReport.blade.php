@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<main>
    <div class="px-4 md:px-10 py-8">

        {{-- ========== HEADER ========== --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Sales Reporting</h2>
                <p class="text-gray-500 mt-1 text-sm md:text-base">
                    Track revenue, orders, and product performance
                </p>
            </div>
            <a href="{{ route('admin.salesreport.export.csv', ['period' => $period, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
               class="px-4 py-2 bg-white border rounded-lg text-sm text-gray-600 hover:bg-pink-200 hover:text-pink-600 transition">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>
        </div>

        {{-- ========== FILTERS ========== --}}
        <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm mb-8 border-pink-100">
            <form method="GET" action="{{ route('admin.salesreport') }}" id="filterForm">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">

                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-calendar text-xl text-pink-500"></i>
                        <span class="font-medium text-sm md:text-base">Report Period:</span>
                    </div>

                    {{-- Period Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label)
                            <a href="{{ route('admin.salesreport', ['period' => $key]) }}"
                               class="px-5 py-2 border rounded-lg text-sm md:text-base transition
                                      {{ $period === $key && !request('start_date') ? 'bg-pink-600 text-white border-pink-600' : 'border-gray-300 text-gray-600 hover:bg-pink-100 hover:text-pink-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Date Range --}}
                    <div class="flex flex-wrap items-center gap-2 lg:ml-auto">
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-300 outline-none">
                        <span class="text-gray-400 text-sm">to</span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-pink-300 outline-none">
                        <input type="hidden" name="period" value="custom">
                        <button type="submit"
                                class="px-4 py-2 bg-pink-500 text-white rounded-lg text-sm hover:bg-pink-600 transition">
                            Apply
                        </button>
                    </div>
                </div>
            </form>

            <div class="flex justify-between items-center mt-3">
                <p class="text-xs text-gray-500">
                    Showing:
                    <span class="font-semibold text-gray-700">
                        {{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}
                    </span>
                </p>
                <p class="text-xs text-gray-400">
                    Last updated: <span class="text-gray-700">{{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                </p>
            </div>
        </div>

        {{-- ========== STATS CARDS ========== --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">

            {{-- Revenue --}}
            <div class="p-4 md:p-6 border rounded-xl bg-gradient-to-br from-pink-50 to-white border-pink-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs md:text-sm text-gray-500">Total Revenue</p>
                    <div class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                        <i class="fas fa-peso-sign text-pink-500 text-sm"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-3xl font-bold">₱{{ number_format($totalRevenue, 2) }}</h3>
                <p class="text-xs mt-1">
                    @if($revenueGrowth >= 0)
                        <span class="text-green-600 font-semibold">
                            <i class="fas fa-arrow-up text-xs"></i> {{ $revenueGrowth }}%
                        </span>
                    @else
                        <span class="text-red-500 font-semibold">
                            <i class="fas fa-arrow-down text-xs"></i> {{ abs($revenueGrowth) }}%
                        </span>
                    @endif
                    <span class="text-gray-400 text-xs">vs prev period</span>
                </p>
            </div>

            {{-- Orders --}}
            <div class="p-4 md:p-6 border rounded-xl bg-gradient-to-br from-blue-50 to-white border-blue-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs md:text-sm text-gray-500">Total Orders</p>
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-blue-500 text-sm"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-3xl font-bold">{{ $totalOrders }}</h3>
                <p class="text-xs mt-1">
                    @if($orderGrowth >= 0)
                        <span class="text-green-600 font-semibold">
                            <i class="fas fa-arrow-up text-xs"></i> {{ $orderGrowth }}%
                        </span>
                    @else
                        <span class="text-red-500 font-semibold">
                            <i class="fas fa-arrow-down text-xs"></i> {{ abs($orderGrowth) }}%
                        </span>
                    @endif
                    <span class="text-gray-400 text-xs">vs prev period</span>
                </p>
            </div>

            {{-- Completed --}}
            <div class="p-4 md:p-6 border rounded-xl bg-gradient-to-br from-green-50 to-white border-green-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs md:text-sm text-gray-500">Completed</p>
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-3xl font-bold">{{ $completedOrders }}</h3>
                <p class="text-xs mt-1 text-gray-400">{{ $cancelledOrders }} cancelled</p>
            </div>

            {{-- AOV --}}
            <div class="p-4 md:p-6 border rounded-xl bg-gradient-to-br from-purple-50 to-white border-purple-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs md:text-sm text-gray-500">Avg Order Value</p>
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-500 text-sm"></i>
                    </div>
                </div>
                <h3 class="text-xl md:text-3xl font-bold">₱{{ number_format($avgOrderValue, 2) }}</h3>
                <p class="text-xs mt-1 text-gray-400">per order</p>
            </div>
        </div>

        {{-- ========== SALES TREND CHART ========== --}}
        <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm mb-8 border-pink-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-area text-pink-500 mr-2"></i> Sales Trend
                </h3>
                <div class="flex gap-3">
                    <span class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="w-3 h-3 rounded-full bg-pink-500 inline-block"></span> Revenue
                    </span>
                    <span class="flex items-center gap-1 text-xs text-gray-500">
                        <span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span> Orders
                    </span>
                </div>
            </div>

            @if($totalOrders > 0)
                <div class="relative" style="height: 320px;">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center text-gray-400 py-16">
                    <i class="fas fa-chart-line text-4xl mb-3 text-pink-300"></i>
                    <p class="text-sm">No sales data for this period</p>
                    <p class="text-xs mt-1">Try selecting a wider date range</p>
                </div>
            @endif
        </div>

        {{-- ========== TWO COLUMNS ========== --}}
        <div class="grid md:grid-cols-2 gap-6 mb-8">

            {{-- TOP PRODUCTS --}}
            <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm border-pink-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top Selling Products
                </h3>

                @if($topProducts->isEmpty())
                    <div class="text-center text-gray-400 py-8">
                        <i class="fas fa-box-open text-3xl mb-2"></i>
                        <p class="text-sm">No product data for this period</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($topProducts as $index => $product)
                            @php
                                $maxRevenue = $topProducts->first()->total_revenue ?: 1;
                                $barWidth   = ($product->total_revenue / $maxRevenue) * 100;
                                $medal      = match($index) {
                                    0 => '🥇', 1 => '🥈', 2 => '🥉',
                                    default => '<span class="text-gray-400 text-xs font-bold">#' . ($index + 1) . '</span>'
                                };
                                $imgSrc = $product->imageURL
                                    ? asset('storage/products/' . $product->imageURL)
                                    : asset('images/sample_food.jpg');
                            @endphp

                            <div class="flex items-center gap-3">
                                <span class="w-8 text-center text-lg">{!! $medal !!}</span>
                                <img src="{{ $imgSrc }}" alt="{{ $product->name }}"
                                     class="w-10 h-10 rounded-lg object-cover border"
                                     onerror="this.src='{{ asset('images/sample_food.jpg') }}'">
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                                        <p class="text-sm font-bold text-pink-600 ml-2 whitespace-nowrap">
                                            ₱{{ number_format($product->total_revenue, 2) }}
                                        </p>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                                        <div class="h-1.5 rounded-full bg-gradient-to-r from-pink-400 to-pink-600"
                                             style="width: {{ $barWidth }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ (int) $product->total_units }} units sold</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PAYMENT SUMMARY --}}
            <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm border-pink-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-credit-card text-pink-500 mr-2"></i> Payment Summary
                </h3>

                {{-- By Method --}}
                <p class="text-xs text-gray-500 mb-3 uppercase tracking-wider font-semibold">By Method</p>
                @php
                    $methodTotal = $gcashTotal + $codTotal;
                    $gcashPct    = $methodTotal > 0 ? round(($gcashTotal / $methodTotal) * 100, 1) : 0;
                    $codPct      = $methodTotal > 0 ? round(($codTotal / $methodTotal) * 100, 1) : 0;
                @endphp

                <div class="space-y-3 mb-5">
                    {{-- GCash --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> GCash
                            </span>
                            <span class="font-semibold">₱{{ number_format($gcashTotal, 2) }} ({{ $gcashPct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full bg-blue-500" style="width: {{ $gcashPct }}%"></div>
                        </div>
                    </div>

                    {{-- COD --}}
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> COD
                            </span>
                            <span class="font-semibold">₱{{ number_format($codTotal, 2) }} ({{ $codPct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full bg-green-500" style="width: {{ $codPct }}%"></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- By Status --}}
                <p class="text-xs text-gray-500 mb-3 uppercase tracking-wider font-semibold">By Status</p>
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center p-3 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-2xl font-bold text-green-600">{{ $paidCount }}</p>
                        <p class="text-xs text-green-700 font-medium mt-1">Paid</p>
                        <p class="text-xs text-green-500">₱{{ number_format($paidTotal, 2) }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                        <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
                        <p class="text-xs text-yellow-700 font-medium mt-1">Pending</p>
                        <p class="text-xs text-yellow-500">₱{{ number_format($pendingTotal, 2) }}</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-red-50 border border-red-200">
                        <p class="text-2xl font-bold text-red-600">{{ $rejectedCount }}</p>
                        <p class="text-xs text-red-700 font-medium mt-1">Rejected</p>
                        <p class="text-xs text-red-500">₱{{ number_format($rejectedTotal, 2) }}</p>
                    </div>
                </div>

                {{-- Donut --}}
                @if($paidTotal + $pendingTotal + $rejectedTotal > 0)
                    <div class="mt-5 flex justify-center" style="height: 200px;">
                        <canvas id="paymentDonutChart"></canvas>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========== ORDER STATUS BREAKDOWN ========== --}}
        <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm mb-8 border-pink-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-tasks text-pink-500 mr-2"></i> Order Status Breakdown
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                @php
                    $allStatuses = [
                        'Pending'          => ['icon' => 'fa-clock',        'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-600', 'iconC' => 'text-yellow-500'],
                        'Confirmed'        => ['icon' => 'fa-check',        'bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-600',   'iconC' => 'text-blue-500'],
                        'Preparing'        => ['icon' => 'fa-utensils',     'bg' => 'bg-orange-50',  'border' => 'border-orange-200', 'text' => 'text-orange-600', 'iconC' => 'text-orange-500'],
                        'Out for Delivery' => ['icon' => 'fa-truck',        'bg' => 'bg-indigo-50',  'border' => 'border-indigo-200', 'text' => 'text-indigo-600', 'iconC' => 'text-indigo-500'],
                        'Done'             => ['icon' => 'fa-check-double', 'bg' => 'bg-green-50',   'border' => 'border-green-200',  'text' => 'text-green-600',  'iconC' => 'text-green-500'],
                        'Cancelled'        => ['icon' => 'fa-times-circle', 'bg' => 'bg-red-50',     'border' => 'border-red-200',    'text' => 'text-red-600',    'iconC' => 'text-red-500'],
                    ];
                @endphp

                @foreach($allStatuses as $statusName => $style)
                    <div class="text-center p-4 rounded-xl {{ $style['bg'] }} border {{ $style['border'] }} hover:shadow-md transition">
                        <i class="fas {{ $style['icon'] }} {{ $style['iconC'] }} text-xl mb-2 block"></i>
                        <p class="text-2xl font-bold {{ $style['text'] }}">{{ $statusBreakdown[$statusName] ?? 0 }}</p>
                        <p class="text-xs {{ $style['text'] }} font-medium mt-1">{{ $statusName }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ========== DEBUG INFO (remove this later) ========== --}}
        <div class="border rounded-xl p-4 bg-gray-50 shadow-sm mb-8 border-gray-200">
            <h4 class="font-semibold text-gray-600 mb-2 text-sm">🔧 Debug Info (Remove after confirming it works)</h4>
            <div class="text-xs text-gray-500 space-y-1">
                <p><strong>Period:</strong> {{ $period }}</p>
                <p><strong>Date Range:</strong> {{ $from->format('Y-m-d H:i:s') }} → {{ $to->format('Y-m-d H:i:s') }}</p>
                <p><strong>Total Orders Found:</strong> {{ $totalOrders }}</p>
                <p><strong>Total Revenue:</strong> ₱{{ number_format($totalRevenue, 2) }}</p>
                <p><strong>Top Products Count:</strong> {{ $topProducts->count() }}</p>
                <p><strong>Status Breakdown:</strong> {{ json_encode($statusBreakdown) }}</p>
            </div>
        </div>

    </div>
</main>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================
    // SALES TREND CHART
    // ============================
    const trendCanvas = document.getElementById('salesTrendChart');
    if (trendCanvas) {
        const ctx = trendCanvas.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    {
                        label: 'Revenue (₱)',
                        data: @json($trendRevenue),
                        borderColor: '#EC4899',
                        backgroundColor: 'rgba(236,72,153,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders',
                        data: @json($trendOrders),
                        borderColor: '#60A5FA',
                        backgroundColor: 'rgba(96,165,250,0.08)',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.datasetIndex === 0) {
                                    return 'Revenue: ₱' + parseFloat(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits: 2});
                                }
                                return 'Orders: ' + ctx.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, maxRotation: 45 }
                    },
                    y: {
                        type: 'linear', position: 'left',
                        grid: { color: '#F3F4F6' },
                        ticks: {
                            callback: function(v) {
                                return '₱' + parseFloat(v).toLocaleString('en-PH', {minimumFractionDigits: 0});
                            },
                            font: { size: 11 }
                        },
                        beginAtZero: true,
                    },
                    y1: {
                        type: 'linear', position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 11 }, stepSize: 1 },
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // ============================
    // PAYMENT DONUT CHART
    // ============================
    const donutCanvas = document.getElementById('paymentDonutChart');
    if (donutCanvas) {
        new Chart(donutCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Rejected'],
                datasets: [{
                    data: [
                        {{ $paidTotal }},
                        {{ $pendingTotal }},
                        {{ $rejectedTotal }}
                    ],
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': ₱' + parseFloat(ctx.raw).toLocaleString('en-PH', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }

});
</script>
@endsection