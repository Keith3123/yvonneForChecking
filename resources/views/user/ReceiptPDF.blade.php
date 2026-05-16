<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->orderID }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 24px; }
        h2, h3, p { margin: 0; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }
        .dashed { border-top: 1px dashed #d1d5db; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .label { color: #6b7280; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; color: #9ca3af; }
        .right { text-align: right; }
        .center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .section { margin-bottom: 10px; }
        .section-label { font-size: 10px; color: #9ca3af; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .total-row td { font-weight: bold; font-size: 13px; border-top: 1px solid #374151; padding-top: 6px; margin-top: 4px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="section center">
        <h2 style="font-size:15px;">Yvonne's Cakes &amp; Pastries</h2>
        <p class="small">Bacaca Road, Davao City · 0912-345-6789</p>
    </div>

    <hr>

    <div class="section center">
        <p class="small" style="text-transform:uppercase; letter-spacing:1px; font-weight:bold;">Official Receipt</p>
        <p class="small">Order #{{ $order->orderID }}</p>
        <p class="small">{{ $order->orderDate->format('F d, Y h:i A') }}</p>
    </div>

    <hr>

    {{-- DELIVERY INFO --}}
    <div class="section">
        <table>
            <tr>
                <td class="label" width="30%">Delivery Date</td>
                <td>{{ $order->deliveryDate ? $order->deliveryDate->format('F d, Y h:i A') : 'Not Set' }}</td>
            </tr>
            <tr>
                <td class="label">Address</td>
                <td>{{ $order->deliveryAddress }}</td>
            </tr>
        </table>
    </div>

    <hr>

    {{-- ORDER ITEMS --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th align="left" style="font-size:10px; color:#9ca3af; padding-bottom:4px;">Item</th>
                    <th align="center" width="10%" style="font-size:10px; color:#9ca3af; padding-bottom:4px;">Qty</th>
                    <th align="right" width="22%" style="font-size:10px; color:#9ca3af; padding-bottom:4px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>
                        {{ $item->product?->name ?? 'Product' }}
                        @if($item->size)<span class="small"> ({{ $item->size }})</span>@endif
                        @if($item->message)<br><span class="small label">"{{ $item->message }}"</span>@endif
                        @php
                            $inc = is_string($item->includes ?? null)
                                ? json_decode($item->includes, true)
                                : ($item->includes ?? null);
                        @endphp
                        @if(is_array($inc) && count($inc))
                            <br><span class="small label">Includes: {{ implode(', ', $inc) }}</span>
                        @endif
                    </td>
                    <td align="center">{{ $item->qty }}</td>
                    <td align="right">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <hr class="dashed">

    {{-- PAYMENT BREAKDOWN --}}
    @php
        $paymentRecords = $order->payments->count()
            ? $order->payments
            : collect([$order->payment])->filter();
    @endphp
    <div class="section">
        <div class="section-label">Payment breakdown</div>
        <table>
            @foreach($paymentRecords as $pay)
            @php
                $typeLabel = match($pay->paymentType ?? 'fullpayment') {
                    'downpayment'       => 'GCash Downpayment',
                    'remaining_balance' => 'Remaining Balance (On Delivery)',
                    default             => strtoupper($pay->method ?? 'COD') . ' Full Payment',
                };
                $badgeClass = $pay->status === 'approved' ? 'badge-green' : 'badge-yellow';
            @endphp
            <tr>
                <td class="label">{{ $typeLabel }}</td>
                <td align="right" width="25%">₱{{ number_format($pay->amount, 2) }}</td>
                <td align="right" width="18%">
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($pay->status) }}</span>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <hr class="dashed">

    {{-- VAT & TOTAL --}}
    <div class="section">
        <table>
            <tr>
                <td class="label">VATable Sales</td>
                <td align="right">₱{{ number_format($vatableSales, 2) }}</td>
            </tr>
            <tr>
                <td class="label">VAT (12%)</td>
                <td align="right">₱{{ number_format($vatAmount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Amount Due</td>
                <td align="right">₱{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </table>
    </div>

    <hr>

    <p class="small center">Thank you for ordering from Yvonne's Cakes &amp; Pastries!</p>

</body>
</html>