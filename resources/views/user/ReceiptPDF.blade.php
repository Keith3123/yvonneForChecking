<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->orderID }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .text-right { text-align: right; }
        .border-top { border-top: 1px solid #000; margin-top: 5px; padding-top: 5px; }
        .mb-2 { margin-bottom: 2px; }
    </style>
</head>
<body>
    <h2>Yvonne's Cakes & Pastries</h2>
    <p>Bacaca Road, Davao City</p>
    <p>Phone: 0912-345-6789</p>
    <hr>
    <h3>OFFICIAL RECEIPT</h3>
    <p>Order #{{ $order->orderID }}</p>
    <p>{{ $order->orderDate->format('F d, Y h:i A') }}</p>

    <p>Payment: {{ strtoupper($order->payment->method ?? 'COD') }}</p>
    <p>Delivery: {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d h:i A') : 'Not Set' }}</p>
    <p>Address: {{ $order->deliveryAddress }}</p>

    <hr>
    <table width="100%" cellpadding="4">
        <thead>
            <tr>
                <th align="left">Item</th>
                <th align="center">Qty</th>
                <th align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product?->name ?? 'Product' }}</td>
                    <td align="center">{{ $item->qty }}</td>
                    <td align="right">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-top">
        <p>VATable Sales: <span class="text-right">₱{{ number_format($vatableSales, 2) }}</span></p>
        <p>VAT: <span class="text-right">₱{{ number_format($vatAmount, 2) }}</span></p>
        <p><strong>Total: ₱{{ number_format($totalAmount, 2) }}</strong></p>
    </div>
    <hr>
    <p>Thank you for ordering!</p>
</body>
</html>