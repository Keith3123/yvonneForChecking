<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        color: #1a1a1a;
        background: #fff;
        padding: 30px 36px;
    }

    /* ─── HEADER ─── */
    .shop-name {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        color: #be185d;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .shop-subtitle {
        text-align: center;
        font-size: 9px;
        color: #6b7280;
        margin-top: 2px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .divider-thick {
        border: none;
        border-top: 2.5px solid #be185d;
        margin: 10px 0 8px;
    }
    .divider-thin {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 6px 0;
    }

    /* ─── REPORT META ─── */
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .meta-table td { padding: 2px 0; font-size: 9.5px; }
    .meta-label { color: #6b7280; font-weight: 700; width: 120px; }
    .meta-value { color: #111827; font-weight: 700; }
    .meta-right { text-align: right; color: #6b7280; font-size: 9px; }

    /* ─── SUMMARY CARDS ─── */
    .summary-row { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 14px; }
    .summary-card {
        border: 1px solid #e5e7eb;
        border-top: 3px solid #be185d;
        padding: 8px 10px;
        text-align: center;
        vertical-align: top;
    }
    .summary-card.green  { border-top-color: #10b981; }
    .summary-card.blue   { border-top-color: #3b82f6; }
    .summary-card.purple { border-top-color: #8b5cf6; }
    .summary-card.red    { border-top-color: #ef4444; }
    .summary-card.orange { border-top-color: #f97316; }
    .sc-label { font-size: 7.5px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .sc-value { font-size: 14px; font-weight: 700; color: #111827; margin: 3px 0 1px; line-height: 1; }
    .sc-value.pink   { color: #be185d; }
    .sc-value.green  { color: #059669; }
    .sc-value.blue   { color: #2563eb; }
    .sc-value.purple { color: #7c3aed; }
    .sc-value.red    { color: #dc2626; }
    .sc-value.orange { color: #ea580c; }
    .sc-sub { font-size: 7px; color: #9ca3af; margin-top: 2px; }
    .sc-sub.up   { color: #059669; }
    .sc-sub.down { color: #dc2626; }

    /* ─── SECTION TITLE ─── */
    .sec-title {
        font-size: 9px;
        font-weight: 700;
        color: #be185d;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-left: 3px solid #be185d;
        padding-left: 7px;
        margin: 14px 0 7px;
    }

    /* ─── MAIN TABLE ─── */
    .main-tbl { width: 100%; border-collapse: collapse; font-size: 9px; }
    .main-tbl thead tr { background: #be185d; }
    .main-tbl thead th {
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 7px 8px;
        text-align: left;
    }
    .main-tbl thead th.r { text-align: right; }
    .main-tbl thead th.c { text-align: center; }
    .main-tbl tbody tr:nth-child(odd)  td { background: #fff; }
    .main-tbl tbody tr:nth-child(even) td { background: #fdf2f8; }
    .main-tbl tbody td {
        padding: 6px 8px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        color: #374151;
    }
    .main-tbl tbody tr:last-child td { border-bottom: none; }
    .main-tbl tfoot td {
        padding: 7px 8px;
        background: #fff0f6;
        border-top: 2px solid #be185d;
        font-weight: 700;
        font-size: 10px;
        color: #be185d;
    }

    /* ─── STATUS BADGE ─── */
    .badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 20px;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .b-done      { background: #d1fae5; color: #065f46; }
    .b-pending   { background: #fef3c7; color: #92400e; }
    .b-confirmed { background: #dbeafe; color: #1e40af; }
    .b-preparing { background: #ede9fe; color: #5b21b6; }
    .b-delivery  { background: #e0e7ff; color: #3730a3; }
    .b-cancelled { background: #fee2e2; color: #991b1b; }

    /* ─── TWO-COLUMN SECTION ─── */
    .two-col { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 4px; }
    .col-box { vertical-align: top; width: 50%; border: 1px solid #e5e7eb; border-top: 2px solid #be185d; padding: 10px 12px; }
    .col-box-title { font-size: 8px; font-weight: 700; color: #be185d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 7px; padding-bottom: 5px; border-bottom: 1px solid #fce7f3; }
    .inner-tbl { width: 100%; border-collapse: collapse; font-size: 8.5px; }
    .inner-tbl thead th { padding: 4px 5px; text-align: left; font-size: 7.5px; color: #6b7280; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
    .inner-tbl thead th.r { text-align: right; }
    .inner-tbl thead th.c { text-align: center; }
    .inner-tbl tbody td { padding: 4px 5px; border-bottom: 1px solid #f3f4f6; color: #374151; }
    .inner-tbl tbody tr:last-child td { border-bottom: none; }
    .inner-tbl tfoot td { padding: 4px 5px; border-top: 1px solid #fbcfe8; background: #fff0f6; font-weight: 700; color: #be185d; font-size: 8.5px; }
    .inner-tbl tfoot td.r { text-align: right; }

    /* ─── STATUS BREAKDOWN ─── */
    .status-row { width: 100%; border-collapse: separate; border-spacing: 4px; margin-bottom: 14px; }
    .status-cell { text-align: center; padding: 8px 4px; border-radius: 4px; vertical-align: middle; }
    .status-num  { font-size: 18px; font-weight: 700; line-height: 1; }
    .status-lbl  { font-size: 6.5px; font-weight: 700; text-transform: uppercase; margin-top: 3px; letter-spacing: 0.3px; }

    /* ─── PROGRESS BAR ─── */
    .bar-wrap { background: #f3f4f6; border-radius: 4px; height: 6px; width: 100%; margin-top: 3px; }
    .bar-fill  { height: 6px; border-radius: 4px; background: #be185d; }
    .bar-fill.green  { background: #10b981; }
    .bar-fill.blue   { background: #3b82f6; }

    /* ─── KPI BOX ─── */
    .kpi-box {
        border: 1px solid #fce7f3;
        background: #fff0f6;
        padding: 10px 12px;
        margin-bottom: 10px;
    }
    .kpi-row { width: 100%; border-collapse: collapse; }
    .kpi-row td { padding: 3px 0; font-size: 9px; }
    .kpi-label { color: #6b7280; }
    .kpi-val   { text-align: right; font-weight: 700; color: #111827; }
    .kpi-val.up   { color: #059669; }
    .kpi-val.down { color: #dc2626; }

    /* ─── INSIGHT BOX ─── */
    .insight-box {
        border: 1px solid #e0e7ff;
        border-left: 3px solid #6366f1;
        background: #f5f3ff;
        padding: 10px 12px;
        margin-top: 10px;
        font-size: 8.5px;
        color: #374151;
        line-height: 1.5;
    }
    .insight-title { font-size: 8px; font-weight: 700; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .insight-item  { margin-bottom: 4px; }
    .insight-item:last-child { margin-bottom: 0; }
    .bullet { color: #be185d; font-weight: 700; margin-right: 4px; }

    /* ─── TOTAL SUMMARY BOX ─── */
    .total-box {
        background: #be185d;
        color: #fff;
        padding: 12px 16px;
        margin-top: 14px;
        border-radius: 4px;
    }
    .total-box-row { display: table; width: 100%; margin-bottom: 4px; }
    .total-box-label { display: table-cell; font-size: 9px; color: #fce7f3; }
    .total-box-value { display: table-cell; text-align: right; font-size: 10px; font-weight: 700; color: #fff; }
    .total-box-grand { display: table; width: 100%; margin-top: 6px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 6px; }
    .total-box-grand-label { display: table-cell; font-size: 12px; font-weight: 700; color: #fff; }
    .total-box-grand-value { display: table-cell; text-align: right; font-size: 15px; font-weight: 700; color: #fff; }

    /* ─── SIGNATURE ─── */
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
    .sig-cell { width: 33.33%; text-align: center; vertical-align: bottom; padding: 0 10px; }
    .sig-line { border-top: 1px solid #374151; padding-top: 5px; margin-top: 30px; font-size: 8.5px; color: #374151; font-weight: 700; }
    .sig-role { font-size: 8px; color: #6b7280; margin-top: 1px; }

    /* ─── FOOTER ─── */
    .footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        display: table;
        width: 100%;
    }
    .footer-l { display: table-cell; font-size: 8px; color: #9ca3af; }
    .footer-r { display: table-cell; text-align: right; font-size: 8px; color: #9ca3af; }

    /* ─── PAGE BREAK ─── */
    .page-break { page-break-after: always; }

    /* ─── UTILS ─── */
    .r { text-align: right; }
    .c { text-align: center; }
    .b { font-weight: 700; }
    .pink { color: #be185d; }
    .gray { color: #6b7280; }
    .sm   { font-size: 8px; }
    .green-txt { color: #059669; }
    .red-txt   { color: #dc2626; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════ --}}
{{-- PAGE 1 — EXECUTIVE SUMMARY & KPIs --}}
{{-- ══════════════════════════════════════════ --}}

{{-- HEADER --}}
<div class="shop-name">&#127874; Yvonne's Cakes &amp; Pastries</div>
<div class="shop-subtitle">Official Sales Report &mdash; Executive Summary &amp; Performance Overview</div>
<hr class="divider-thick">

{{-- REPORT META --}}
<table class="meta-table">
    <tr>
        <td>
            <table style="border-collapse:collapse;">
                <tr>
                    <td class="meta-label">Report Type:</td>
                    <td class="meta-value">Comprehensive Sales Report</td>
                </tr>
                <tr>
                    <td class="meta-label">Period Range:</td>
                    <td class="meta-value">{{ $from->format('M d, Y') }} &mdash; {{ $to->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Period Filter:</td>
                    <td class="meta-value" style="text-transform:capitalize;">{{ $period }}</td>
                </tr>
            </table>
        </td>
        <td class="meta-right">
            Generated: &nbsp;<strong style="color:#111827;">{{ now()->timezone('Asia/Manila')->format('M d, Y') }}</strong><br>
            {{ now()->timezone('Asia/Manila')->format('h:i A') }} PHT &nbsp;|&nbsp; CONFIDENTIAL
        </td>
    </tr>
</table>
<hr class="divider-thin">

{{-- ─── SECTION 1: EXECUTIVE SUMMARY CARDS ─── --}}
<div class="sec-title">&#128200; Executive Summary</div>

@php
    $methodTotal  = $gcashTotal + $codTotal;
    $gcashPct     = $methodTotal > 0 ? round(($gcashTotal / $methodTotal) * 100, 1) : 0;
    $codPct       = $methodTotal > 0 ? round(($codTotal   / $methodTotal) * 100, 1) : 0;

    $allStatusList = ['Pending','Confirmed','Preparing','Out for Delivery','Done','Cancelled'];

    $totalUnits = $topProducts->sum('total_units');

    $completionRate = $totalOrders > 0
        ? round(($completedOrders / $totalOrders) * 100, 1)
        : 0;
    $cancellationRate = $totalOrders > 0
        ? round(($cancelledOrders / $totalOrders) * 100, 1)
        : 0;
@endphp

<table class="summary-row">
    <tr>
        {{-- Total Revenue --}}
        <td class="summary-card">
            <div class="sc-label">Total Revenue</div>
            <div class="sc-value pink">&#8369;{{ number_format($totalRevenue, 2) }}</div>
            @if($revenueGrowth >= 0)
                <div class="sc-sub up">&#9650; {{ $revenueGrowth }}% vs prev</div>
            @else
                <div class="sc-sub down">&#9660; {{ abs($revenueGrowth) }}% vs prev</div>
            @endif
        </td>
        {{-- Total Orders --}}
        <td class="summary-card blue">
            <div class="sc-label">Total Orders</div>
            <div class="sc-value blue">{{ $totalOrders }}</div>
            @if($orderGrowth >= 0)
                <div class="sc-sub up">&#9650; {{ $orderGrowth }}% vs prev</div>
            @else
                <div class="sc-sub down">&#9660; {{ abs($orderGrowth) }}% vs prev</div>
            @endif
        </td>
        {{-- Completed Orders --}}
        <td class="summary-card green">
            <div class="sc-label">Completed Orders</div>
            <div class="sc-value green">{{ $completedOrders }}</div>
            <div class="sc-sub">{{ $completionRate }}% completion rate</div>
        </td>
        {{-- Avg Order Value --}}
        <td class="summary-card purple">
            <div class="sc-label">Avg Order Value</div>
            <div class="sc-value purple">&#8369;{{ number_format($avgOrderValue, 2) }}</div>
            <div class="sc-sub">per order</div>
        </td>
        {{-- Cancelled --}}
        <td class="summary-card red">
            <div class="sc-label">Cancelled Orders</div>
            <div class="sc-value red">{{ $cancelledOrders }}</div>
            <div class="sc-sub">{{ $cancellationRate }}% of total</div>
        </td>
        {{-- Units Sold --}}
        <td class="summary-card orange">
            <div class="sc-label">Units Sold</div>
            <div class="sc-value orange">{{ number_format($totalUnits) }}</div>
            <div class="sc-sub">from active orders</div>
        </td>
    </tr>
</table>

{{-- ─── SECTION 2: KPI — SALES TARGET VS ACTUAL & GROWTH ─── --}}
<div class="sec-title">&#127919; Performance vs. Goals (KPI)</div>

<table class="two-col">
    <tr>
        {{-- Sales Growth --}}
        <td class="col-box">
            <div class="col-box-title">&#128200; Revenue Growth Rate</div>
            <table class="kpi-row">
                <tr>
                    <td class="kpi-label">Current Period Revenue</td>
                    <td class="kpi-val">&#8369;{{ number_format($totalRevenue, 2) }}</td>
                </tr>
                <tr>
                    <td class="kpi-label">vs. Previous Period</td>
                    <td class="kpi-val {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
                        {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                    </td>
                </tr>
                <tr>
                    <td class="kpi-label">Order Growth Rate</td>
                    <td class="kpi-val {{ $orderGrowth >= 0 ? 'up' : 'down' }}">
                        {{ $orderGrowth >= 0 ? '+' : '' }}{{ $orderGrowth }}%
                    </td>
                </tr>
                <tr>
                    <td class="kpi-label">Completion Rate</td>
                    <td class="kpi-val up">{{ $completionRate }}%</td>
                </tr>
                <tr>
                    <td class="kpi-label">Cancellation Rate</td>
                    <td class="kpi-val {{ $cancellationRate > 10 ? 'down' : '' }}">{{ $cancellationRate }}%</td>
                </tr>
            </table>

            {{-- Completion Rate Bar --}}
            <div style="margin-top:8px;">
                <div style="font-size:7.5px;color:#6b7280;margin-bottom:3px;">Completion Rate Progress</div>
                <div class="bar-wrap">
                    <div class="bar-fill green" style="width:{{ min($completionRate,100) }}%;"></div>
                </div>
                <div style="font-size:7px;color:#059669;margin-top:2px;text-align:right;">{{ $completionRate }}% completed</div>
            </div>
        </td>

        {{-- Payment Split --}}
        <td class="col-box">
            <div class="col-box-title">&#128179; Payment Method Distribution</div>
            <table class="kpi-row">
                <tr>
                    <td class="kpi-label">GCash Collections</td>
                    <td class="kpi-val">&#8369;{{ number_format($gcashTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="kpi-label">GCash Share</td>
                    <td class="kpi-val blue">{{ $gcashPct }}%</td>
                </tr>
                <tr>
                    <td class="kpi-label">COD Collections</td>
                    <td class="kpi-val">&#8369;{{ number_format($codTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="kpi-label">COD Share</td>
                    <td class="kpi-val green-txt">{{ $codPct }}%</td>
                </tr>
                <tr>
                    <td class="kpi-label">Paid Payments</td>
                    <td class="kpi-val up">{{ $paidCount }} (&#8369;{{ number_format($paidTotal,2) }})</td>
                </tr>
                <tr>
                    <td class="kpi-label">Pending Payments</td>
                    <td class="kpi-val">{{ $pendingCount }} (&#8369;{{ number_format($pendingTotal,2) }})</td>
                </tr>
                <tr>
                    <td class="kpi-label">Rejected Payments</td>
                    <td class="kpi-val {{ $rejectedCount > 0 ? 'down' : '' }}">{{ $rejectedCount }} (&#8369;{{ number_format($rejectedTotal,2) }})</td>
                </tr>
            </table>
            {{-- GCash Bar --}}
            <div style="margin-top:8px;">
                <div style="font-size:7.5px;color:#6b7280;margin-bottom:2px;">GCash vs COD Split</div>
                <div class="bar-wrap">
                    <div class="bar-fill blue" style="width:{{ $gcashPct }}%;"></div>
                </div>
                <div style="font-size:7px;color:#2563eb;margin-top:2px;">GCash {{ $gcashPct }}% &nbsp;|&nbsp; COD {{ $codPct }}%</div>
            </div>
        </td>
    </tr>
</table>

{{-- ─── SECTION 3: ORDER STATUS BREAKDOWN ─── --}}
<div class="sec-title">&#128203; Order Status Breakdown</div>

@php
    $statusConfig = [
        'Pending'          => ['bg' => '#fef3c7', 'color' => '#92400e'],
        'Confirmed'        => ['bg' => '#dbeafe', 'color' => '#1e40af'],
        'Preparing'        => ['bg' => '#ede9fe', 'color' => '#5b21b6'],
        'Out for Delivery' => ['bg' => '#e0e7ff', 'color' => '#3730a3'],
        'Done'             => ['bg' => '#d1fae5', 'color' => '#065f46'],
        'Cancelled'        => ['bg' => '#fee2e2', 'color' => '#991b1b'],
    ];
@endphp

<table class="status-row">
    <tr>
        @foreach($allStatuses as $st)
        @php
            $cnt = $statusBreakdown[$st] ?? 0;
            $cfg = $statusConfig[$st] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
        @endphp
        <td class="status-cell" style="background:{{ $cfg['bg'] }};">
            <div class="status-num" style="color:{{ $cfg['color'] }};">{{ $cnt }}</div>
            <div class="status-lbl" style="color:{{ $cfg['color'] }};">{{ $st }}</div>
        </td>
        @endforeach
    </tr>
</table>

{{-- ─── SECTION 4: TOP SELLING PRODUCTS ─── --}}
<div class="sec-title">&#127942; Top Selling Products (Revenue Breakdown)</div>

<table class="main-tbl">
    <thead>
        <tr>
            <th style="width:5%;" class="c">Rank</th>
            <th>Product Name</th>
            <th class="r" style="width:15%;">Units Sold</th>
            <th class="r" style="width:20%;">Revenue</th>
            <th style="width:25%;">Revenue Share</th>
        </tr>
    </thead>
    <tbody>
        @php $topRevTotal = $topProducts->sum('total_revenue') ?: 1; @endphp
        @forelse($topProducts as $i => $prod)
        @php
            $share = round(($prod->total_revenue / $topRevTotal) * 100, 1);
            $medal = match($i) { 0 => '&#127941;', 1 => '&#129352;', 2 => '&#129353;', default => '#'.($i+1) };
        @endphp
        <tr>
            <td class="c b">{!! $medal !!}</td>
            <td class="b">{{ $prod->name }}</td>
            <td class="r">{{ number_format($prod->total_units) }}</td>
            <td class="r b pink">&#8369;{{ number_format($prod->total_revenue, 2) }}</td>
            <td>
                <div class="bar-wrap">
                    <div class="bar-fill" style="width:{{ $share }}%;"></div>
                </div>
                <div style="font-size:7px;color:#be185d;margin-top:1px;">{{ $share }}%</div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="c gray" style="padding:12px;">No product sales data for this period.</td>
        </tr>
        @endforelse
    </tbody>
    @if($topProducts->count() > 0)
    <tfoot>
        <tr>
            <td colspan="2" class="pink b">TOTAL (Top Products)</td>
            <td class="r pink b">{{ number_format($totalUnits) }} units</td>
            <td class="r pink b" style="font-size:11px;">&#8369;{{ number_format($topProducts->sum('total_revenue'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- ─── INSIGHTS BOX ─── --}}
<div class="insight-box" style="margin-top:14px;">
    <div class="insight-title">&#128161; Auto-Generated Insights &amp; Recommendations</div>

    @php
        $bestProduct = $topProducts->first();
        $topPayMethod = $gcashTotal >= $codTotal ? 'GCash' : 'COD';
        $topPayAmt    = $gcashTotal >= $codTotal ? $gcashTotal : $codTotal;
    @endphp

    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        @if($revenueGrowth > 0)
            <strong>Revenue is up {{ $revenueGrowth }}%</strong> compared to the previous period. Continue current promotions and maintain product quality to sustain this growth.
        @elseif($revenueGrowth < 0)
            <strong>Revenue declined by {{ abs($revenueGrowth) }}%</strong> vs. the previous period. Consider running targeted promos, bundle deals, or loyalty rewards to recover sales.
        @else
            Revenue is <strong>stable</strong> compared to the previous period. Look for upsell opportunities or new product launches to drive growth.
        @endif
    </div>

    @if($bestProduct)
    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        <strong>{{ $bestProduct->name }}</strong> is the best-selling product this period with &#8369;{{ number_format($bestProduct->total_revenue, 2) }} in revenue ({{ number_format($bestProduct->total_units) }} units). Ensure adequate stock and consider featuring it in marketing materials.
    </div>
    @endif

    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        <strong>{{ $topPayMethod }}</strong> is the dominant payment method at {{ $gcashTotal >= $codTotal ? $gcashPct : $codPct }}% of transactions (&#8369;{{ number_format($topPayAmt, 2) }}). 
        @if($topPayMethod === 'GCash')
            Promote GCash QR prominently to maintain this cashless trend.
        @else
            Consider offering GCash discounts to encourage digital payments and reduce cash-handling risks.
        @endif
    </div>

    @if($cancellationRate > 10)
    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        <strong style="color:#dc2626;">Cancellation rate is at {{ $cancellationRate }}%</strong> — above the 10% threshold. Review order fulfillment issues, customer communication, and delivery lead times to reduce cancellations.
    </div>
    @endif

    @if($rejectedCount > 0)
    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        <strong>{{ $rejectedCount }} payment(s)</strong> were rejected totalling &#8369;{{ number_format($rejectedTotal, 2) }}. Follow up with affected customers to recover these orders and offer re-payment assistance.
    </div>
    @endif

    <div class="insight-item">
        <span class="bullet">&#9654;</span>
        <strong>Recommended next steps:</strong> Review low-selling products for potential repricing or removal, explore new delivery time slots based on peak order hours, and consider a loyalty card program to increase repeat customers.
    </div>
</div>

{{-- FOOTER PAGE 1 --}}
<div class="footer">
    <div class="footer-l">&#127874; Yvonne's Cakes &amp; Pastries &mdash; CONFIDENTIAL &mdash; For Internal Use Only &mdash; Page 1 of 2</div>
    <div class="footer-r">Generated: {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }} PHT</div>
</div>

{{-- PAGE BREAK --}}
<div class="page-break"></div>

{{-- ══════════════════════════════════════════ --}}
{{-- PAGE 2 — ORDER TRANSACTIONS --}}
{{-- ══════════════════════════════════════════ --}}

{{-- HEADER --}}
<div class="shop-name">&#127874; Yvonne's Cakes &amp; Pastries</div>
<div class="shop-subtitle">Official Sales Report &mdash; Detailed Order Transactions</div>
<hr class="divider-thick">

{{-- REPORT META --}}
<table class="meta-table">
    <tr>
        <td>
            <table style="border-collapse:collapse;">
                <tr>
                    <td class="meta-label">Report Type:</td>
                    <td class="meta-value">Detailed Order Transactions</td>
                </tr>
                <tr>
                    <td class="meta-label">Period Range:</td>
                    <td class="meta-value">{{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</td>
                </tr>
            </table>
        </td>
        <td class="meta-right">
            Generated Date: &nbsp;<strong style="color:#111827;">{{ now()->timezone('Asia/Manila')->format('M d, Y') }}</strong><br>
            {{ now()->timezone('Asia/Manila')->format('h:i A') }} PHT &nbsp;|&nbsp; CONFIDENTIAL
        </td>
    </tr>
</table>
<hr class="divider-thin">

{{-- ORDER TABLE --}}
<div class="sec-title">&#128203; Order Transactions</div>
<p class="gray sm" style="margin-bottom:7px;">
    {{ $orders->count() }} order(s) found &nbsp;&bull;&nbsp; Sorted by date descending &nbsp;&bull;&nbsp; Cancelled orders excluded from grand total
</p>

@php
    $badgeMap = [
        'Done'             => 'b-done',
        'Pending'          => 'b-pending',
        'Confirmed'        => 'b-confirmed',
        'Preparing'        => 'b-preparing',
        'Out for Delivery' => 'b-delivery',
        'Cancelled'        => 'b-cancelled',
    ];
@endphp

<table class="main-tbl">
    <thead>
        <tr>
            <th style="width:8%">Order ID</th>
            <th style="width:12%">Date</th>
            <th>Customer</th>
            <th class="c" style="width:6%">Items</th>
            <th style="width:10%">Method</th>
            <th style="width:10%">Pay Status</th>
            <th style="width:12%">Status</th>
            <th class="r" style="width:13%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders->sortByDesc('orderDate') as $order)
        @php
            $badge    = $badgeMap[$order->status] ?? 'b-pending';
            $customer = $order->customer
                ? trim($order->customer->firstName . ' ' . $order->customer->lastName)
                : 'Guest';
            $payMethod = $order->payment->method ?? 'COD';
            $payStatus = $order->payment->status ?? 'pending';
            $payBadge  = match($payStatus) {
                'approved' => 'b-done',
                'rejected' => 'b-cancelled',
                default    => 'b-pending',
            };
        @endphp
        <tr>
            <td class="b pink">#{{ $order->orderID }}</td>
            <td class="gray sm">{{ $order->orderDate ? $order->orderDate->format('M d, Y') : 'N/A' }}</td>
            <td>{{ $customer }}</td>
            <td class="c">{{ $order->orderItems->sum('qty') }}</td>
            <td>{{ $payMethod }}</td>
            <td><span class="badge {{ $payBadge }}">{{ ucfirst($payStatus) }}</span></td>
            <td><span class="badge {{ $badge }}">{{ $order->status }}</span></td>
            <td class="r b pink">&#8369;{{ number_format((float)$order->totalAmount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="c gray" style="padding:16px;">No orders found for this period.</td>
        </tr>
        @endforelse
    </tbody>
    @if($orders->count() > 0)
    <tfoot>
        <tr>
            <td colspan="6"></td>
            <td class="pink b" style="font-size:8.5px;text-transform:uppercase;letter-spacing:0.5px;">Grand Total</td>
            <td class="r b pink" style="font-size:11px;">&#8369;{{ number_format($totalRevenue, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- TOTAL SUMMARY BOX --}}
<div class="total-box" style="margin-top:16px;">
    <div class="total-box-row">
        <div class="total-box-label">Total Orders in this Report</div>
        <div class="total-box-value">{{ $orders->count() }} order(s)</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">Completed Orders</div>
        <div class="total-box-value">{{ $completedOrders }} order(s)</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">Cancelled Orders</div>
        <div class="total-box-value">{{ $cancelledOrders }} order(s)</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">GCash Collections</div>
        <div class="total-box-value">&#8369;{{ number_format($gcashTotal, 2) }}</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">COD Collections</div>
        <div class="total-box-value">&#8369;{{ number_format($codTotal, 2) }}</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">Approved Payments</div>
        <div class="total-box-value">&#8369;{{ number_format($paidTotal, 2) }} ({{ $paidCount }} txns)</div>
    </div>
    <div class="total-box-row">
        <div class="total-box-label">Pending Payments</div>
        <div class="total-box-value">&#8369;{{ number_format($pendingTotal, 2) }} ({{ $pendingCount }} txns)</div>
    </div>
    <div class="total-box-grand">
        <div class="total-box-grand-label">TOTAL REVENUE (EXCL. CANCELLED):</div>
        <div class="total-box-grand-value">PHP {{ number_format($totalRevenue, 2) }}</div>
    </div>
</div>

{{-- SIGNATURE LINE --}}
<table class="sig-table">
    <tr>
        <td class="sig-cell">
            <div class="sig-line">Prepared By / Date</div>
            <div class="sig-role">Sales Staff</div>
        </td>
        <td class="sig-cell">
            <div class="sig-line">Verified By / Date</div>
            <div class="sig-role">Accountant / Bookkeeper</div>
        </td>
        <td class="sig-cell">
            <div class="sig-line">Approved By / Date</div>
            <div class="sig-role">Owner / Manager</div>
        </td>
    </tr>
</table>

{{-- FOOTER PAGE 2 --}}
<div class="footer">
    <div class="footer-l">&#127874; Yvonne's Cakes &amp; Pastries &mdash; CONFIDENTIAL &mdash; For Internal Use Only &mdash; Page 2 of 2</div>
    <div class="footer-r">Generated: {{ now()->timezone('Asia/Manila')->format('M d, Y h:i A') }} PHT</div>
</div>

</body>
</html>