@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="bg-gray-50 min-h-screen">
  <main class="px-4 md:px-8 py-6 md:py-8">

    {{-- ========== HEADER ========== --}}
    <div class="mb-8">
      <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard Overview</h2>
      <p class="text-gray-500 mt-1 text-sm">
        Welcome back! Here's what's happening today —
        <span class="font-medium text-gray-700">{{ now()->timezone('Asia/Manila')->format('l, M d, Y') }}</span>
      </p>
    </div>

    {{-- ========== TODAY'S HIGHLIGHTS ========== --}}
    <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-2xl p-6 md:p-8 mb-8 text-white shadow-lg">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
        <div>
          <h3 class="text-lg font-semibold opacity-90">Today's Performance</h3>
          <p class="text-sm opacity-75">{{ now()->timezone('Asia/Manila')->format('F d, Y') }}</p>
        </div>
        <a href="{{ route('admin.salesreport') }}"
           class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition backdrop-blur-sm">
          <i class="fas fa-chart-bar mr-1"></i> View Full Report
        </a>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        {{-- Today Revenue --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
          <p class="text-sm opacity-80">Revenue</p>
          <p class="text-2xl md:text-3xl font-bold mt-1">₱{{ number_format($todayRevenue, 2) }}</p>
          <p class="text-xs mt-1">
            @if($todayRevenueGrowth >= 0)
              <i class="fas fa-arrow-up"></i> {{ $todayRevenueGrowth }}%
            @else
              <i class="fas fa-arrow-down"></i> {{ abs($todayRevenueGrowth) }}%
            @endif
            vs yesterday
          </p>
        </div>

        {{-- Today Orders --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
          <p class="text-sm opacity-80">Orders</p>
          <p class="text-2xl md:text-3xl font-bold mt-1">{{ $todayOrders }}</p>
          <p class="text-xs mt-1">
            @if($todayOrderGrowth >= 0)
              <i class="fas fa-arrow-up"></i> {{ $todayOrderGrowth }}%
            @else
              <i class="fas fa-arrow-down"></i> {{ abs($todayOrderGrowth) }}%
            @endif
            vs yesterday
          </p>
        </div>

        {{-- Today GCash --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
          <p class="text-sm opacity-80">GCash</p>
          <p class="text-2xl md:text-3xl font-bold mt-1">₱{{ number_format($todayGcash, 2) }}</p>
          <p class="text-xs mt-1 opacity-75">online payments</p>
        </div>

        {{-- Today COD --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4">
          <p class="text-sm opacity-80">COD</p>
          <p class="text-2xl md:text-3xl font-bold mt-1">₱{{ number_format($todayCod, 2) }}</p>
          <p class="text-xs mt-1 opacity-75">cash on delivery</p>
        </div>
      </div>
    </div>

    {{-- ========== STAT CARDS ========== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">

      {{-- Total Revenue --}}
      <div class="bg-white border border-pink-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <p class="text-gray-500 text-sm">Total Revenue</p>
          <div class="bg-pink-50 rounded-lg p-2">
            <i class="fas fa-wallet text-pink-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-3">₱{{ number_format($totalRevenue, 2) }}</h3>
        <p class="text-gray-400 text-xs mt-1">{{ $completedOrders }} completed orders</p>
      </div>

      {{-- Pending Orders --}}
      <div class="bg-white border border-yellow-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <p class="text-gray-500 text-sm">Pending Orders</p>
          <div class="bg-yellow-50 rounded-lg p-2">
            <i class="fas fa-clock text-yellow-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-3">{{ $pendingOrders }}</h3>
        <p class="text-gray-400 text-xs mt-1">needs attention</p>
      </div>

      {{-- Active Paluwagan --}}
      <div class="bg-white border border-blue-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <p class="text-gray-500 text-sm">Active Paluwagan</p>
          <div class="bg-blue-50 rounded-lg p-2">
            <i class="fas fa-hand-holding-usd text-blue-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-3">{{ $activePaluwaganCount }}</h3>
        <p class="text-gray-400 text-xs mt-1">₱{{ number_format($activePaluwagan, 2) }} total value</p>
      </div>

      {{-- Low Stock --}}
      <div class="bg-white border {{ $lowStock > 0 || $outOfStock > 0 ? 'border-red-200' : 'border-green-100' }} p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <p class="text-gray-500 text-sm">Inventory Alerts</p>
          <div class="{{ $lowStock > 0 || $outOfStock > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg p-2">
            <i class="fas fa-box-open {{ $lowStock > 0 || $outOfStock > 0 ? 'text-red-500' : 'text-green-500' }}"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-3">{{ $lowStock + $outOfStock }}</h3>
        <p class="text-gray-400 text-xs mt-1">
          {{ $lowStock }} low · {{ $outOfStock }} out of stock
        </p>
      </div>
    </div>

    {{-- ========== CHART + ORDER STATUS ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

      {{-- WEEKLY CHART --}}
      <div class="lg:col-span-2 bg-white border border-pink-100 p-6 rounded-xl shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Weekly Revenue</h3>
            <p class="text-sm text-gray-400">Last 7 days performance</p>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-pink-600">₱{{ number_format($weekRevenue, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $weekOrders }} orders this week</p>
          </div>
        </div>
        <div style="height: 280px;">
          <canvas id="weeklyChart"></canvas>
        </div>
      </div>

      {{-- ORDER STATUS --}}
      <div class="bg-white border border-pink-100 p-6 rounded-xl shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Order Status</h3>
        <p class="text-sm text-gray-400 mb-4">All time breakdown</p>

        <div class="mb-4" style="height: 180px;">
          <canvas id="statusDonutChart"></canvas>
        </div>

        <div class="space-y-2">
          @php
            $statusColors = [
              'Pending'          => ['dot' => 'bg-yellow-400', 'text' => 'text-yellow-600'],
              'Confirmed'        => ['dot' => 'bg-blue-400',   'text' => 'text-blue-600'],
              'Preparing'        => ['dot' => 'bg-orange-400', 'text' => 'text-orange-600'],
              'Out for Delivery' => ['dot' => 'bg-indigo-400', 'text' => 'text-indigo-600'],
              'Done'             => ['dot' => 'bg-green-400',  'text' => 'text-green-600'],
              'Cancelled'        => ['dot' => 'bg-red-400',    'text' => 'text-red-600'],
            ];
          @endphp

          @foreach($statusColors as $status => $colors)
            <div class="flex justify-between items-center text-sm">
              <span class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $colors['dot'] }} inline-block"></span>
                {{ $status }}
              </span>
              <span class="font-semibold {{ $colors['text'] }}">{{ $statusBreakdown[$status] ?? 0 }}</span>
            </div>
          @endforeach

          <div class="flex justify-between items-center text-sm border-t pt-2 mt-2">
            <span class="font-semibold text-gray-700">Total</span>
            <span class="font-bold text-gray-800">{{ $totalOrders }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== THREE COLUMNS: RECENT ORDERS + TOP PRODUCTS + INVENTORY ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

      {{-- RECENT ORDERS --}}
      <div class="lg:col-span-2 bg-white border border-pink-100 p-6 rounded-xl shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
            <p class="text-sm text-gray-400">Latest 8 orders</p>
          </div>
          <a href="{{ route('admin.orders') }}"
             class="text-pink-500 hover:text-pink-600 text-sm font-medium">
            View All <i class="fas fa-arrow-right ml-1"></i>
          </a>
        </div>

        @if($recentOrders->count() > 0)
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                  <th class="py-3 text-left">Order</th>
                  <th class="py-3 text-left">Customer</th>
                  <th class="py-3 text-left">Status</th>
                  <th class="py-3 text-left">Date</th>
                  <th class="py-3 text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentOrders as $order)
                  @php
                    $statusBadge = match($order->status) {
                      'Done'      => 'bg-green-100 text-green-700',
                      'Cancelled' => 'bg-red-100 text-red-700',
                      'Pending'   => 'bg-yellow-100 text-yellow-700',
                      'Confirmed' => 'bg-blue-100 text-blue-700',
                      'Preparing' => 'bg-orange-100 text-orange-700',
                      'Out for Delivery' => 'bg-indigo-100 text-indigo-700',
                      default     => 'bg-gray-100 text-gray-700',
                    };
                  @endphp
                  <tr class="border-b border-gray-50 hover:bg-pink-50/50 transition">
                    <td class="py-3">
                      <span class="font-semibold text-gray-800">#{{ $order->orderID }}</span>
                    </td>
                    <td class="py-3 text-gray-600">
                      {{ $order->customer ? $order->customer->firstName . ' ' . $order->customer->lastName : 'Guest' }}
                    </td>
                    <td class="py-3">
                      <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadge }}">
                        {{ $order->status }}
                      </span>
                    </td>
                    <td class="py-3 text-gray-500 text-xs">
                      {{ \Carbon\Carbon::parse($order->orderDate)->timezone('Asia/Manila')->format('M d, g:i A') }}
                    </td>
                    <td class="py-3 text-right font-semibold text-gray-800">
                      ₱{{ number_format($order->totalAmount, 2) }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center text-gray-400 py-12">
            <i class="fas fa-receipt text-3xl mb-2"></i>
            <p class="text-sm">No orders yet</p>
          </div>
        @endif
      </div>

      {{-- RIGHT COLUMN: Top Products + Quick Stats --}}
      <div class="space-y-6">

        {{-- TOP PRODUCTS THIS MONTH --}}
        <div class="bg-white border border-pink-100 p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-1">Top Products</h3>
          <p class="text-sm text-gray-400 mb-4">This month's best sellers</p>

          @if($topProducts->isEmpty())
            <div class="text-center text-gray-400 py-6">
              <i class="fas fa-box-open text-2xl mb-2"></i>
              <p class="text-sm">No sales this month</p>
            </div>
          @else
            <div class="space-y-3">
              @foreach($topProducts as $i => $product)
                @php
                  $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '#' . ($i + 1) };
                  $img = $product->imageURL
                    ? asset('storage/products/' . $product->imageURL)
                    : asset('images/sample_food.jpg');
                @endphp
                <div class="flex items-center gap-3">
                  <span class="w-6 text-center text-sm">{{ $medal }}</span>
                  <img src="{{ $img }}" class="w-9 h-9 rounded-lg object-cover border"
                       onerror="this.src='{{ asset('images/sample_food.jpg') }}'">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</p>
                    <p class="text-xs text-gray-400">{{ (int) $product->total_units }} sold</p>
                  </div>
                  <span class="text-sm font-bold text-pink-600">₱{{ number_format($product->total_revenue, 2) }}</span>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- INVENTORY ALERTS --}}
        @if($lowStockItems->count() > 0)
          <div class="bg-white border border-red-200 p-6 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">
              <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i> Low Stock Alert
            </h3>
            <p class="text-sm text-gray-400 mb-4">Items that need restocking</p>

            <div class="space-y-3">
              @foreach($lowStockItems as $item)
                @php
                  $pct = $item->minStockLevel > 0
                    ? round(($item->currentStock / $item->minStockLevel) * 100)
                    : 0;
                  $barColor = $pct <= 25 ? 'bg-red-500' : ($pct <= 50 ? 'bg-orange-400' : 'bg-yellow-400');
                @endphp
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700">{{ $item->name }}</span>
                    <span class="text-xs text-gray-500">
                      {{ $item->currentStock }} / {{ $item->minStockLevel }} {{ $item->unit ?? '' }}
                    </span>
                  </div>
                  <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ min($pct, 100) }}%"></div>
                  </div>
                </div>
              @endforeach
            </div>

            <a href="{{ route('admin.inventory') }}"
               class="block text-center mt-4 text-sm text-red-500 hover:text-red-600 font-medium">
              Manage Inventory <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div>
        @endif

        {{-- QUICK STATS --}}
        <div class="bg-white border border-pink-100 p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Stats</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center p-3 bg-pink-50 rounded-lg">
              <span class="text-sm text-gray-600"><i class="fas fa-users text-pink-400 mr-2"></i> Total Customers</span>
              <span class="font-bold text-gray-800">{{ $totalCustomers }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
              <span class="text-sm text-gray-600"><i class="fas fa-user-plus text-blue-400 mr-2"></i> New This Month</span>
              <span class="font-bold text-gray-800">{{ $newCustomersThisMonth }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
              <span class="text-sm text-gray-600"><i class="fas fa-box text-green-400 mr-2"></i> Total Ingredients</span>
              <span class="font-bold text-gray-800">{{ $totalIngredients }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
              <span class="text-sm text-gray-600"><i class="fas fa-calendar-check text-purple-400 mr-2"></i> This Month Revenue</span>
              <span class="font-bold text-gray-800">₱{{ number_format($monthRevenue, 2) }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>

  </main>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

  // ============================
  // WEEKLY REVENUE CHART
  // ============================
  const weeklyCtx = document.getElementById('weeklyChart');
  if (weeklyCtx) {
    new Chart(weeklyCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: @json($chartLabels),
        datasets: [
          {
            label: 'Revenue',
            data: @json($chartRevenue),
            backgroundColor: 'rgba(236,72,153,0.15)',
            borderColor: '#EC4899',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            yAxisID: 'y',
          },
          {
            label: 'Orders',
            data: @json($chartOrders),
            type: 'line',
            borderColor: '#60A5FA',
            backgroundColor: 'rgba(96,165,250,0.1)',
            fill: false,
            tension: 0.4,
            pointRadius: 4,
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
            ticks: { font: { size: 11 } }
          },
          y: {
            position: 'left',
            grid: { color: '#F3F4F6' },
            ticks: {
              callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v),
              font: { size: 11 }
            },
            beginAtZero: true,
          },
          y1: {
            position: 'right',
            grid: { drawOnChartArea: false },
            ticks: { font: { size: 11 }, stepSize: 1 },
            beginAtZero: true,
          }
        }
      }
    });
  }

  // ============================
  // STATUS DONUT CHART
  // ============================
  const donutCtx = document.getElementById('statusDonutChart');
  if (donutCtx) {
    const statusData = @json($statusBreakdown);
    const labels = Object.keys(statusData);
    const values = Object.values(statusData);

    const colorMap = {
      'Pending': '#FBBF24',
      'Confirmed': '#60A5FA',
      'Preparing': '#FB923C',
      'Out for Delivery': '#818CF8',
      'Done': '#34D399',
      'Cancelled': '#F87171',
    };

    const colors = labels.map(l => colorMap[l] || '#9CA3AF');

    new Chart(donutCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 0,
          hoverOffset: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.label}: ${ctx.raw} orders`
            }
          }
        }
      }
    });
  }

});
</script>
@endsection