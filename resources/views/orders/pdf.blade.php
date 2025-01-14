<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        * {
            font-size: 12px !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Заказ №{{ $order->id }}</h1>
    </div>

    <table style="width: 100%">
        <tbody>
            <tr>
                <td><b>Ф.И.О.:</b> {{ $order->customer_name }}</td>
                <td><b>Телефон:</b> {{ $order->customer_phone }}</td>
            </tr>
            <tr>
                <td><b>Почта:</b> {{ $order->customer_email }}</td>
                <td><b>Адрес:</b> {{ $order->customer_address }}</td>
            </tr>
            <tr>
                <td><b>Комментарий к заказу:</b> {{ $order->comment ? $order->comment : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Детали</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Арт.</th>
                {{-- <th>Фото</th> --}}
                <th>Деталь</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->item_id }}</td>
                    <td>{{ $item->item->vendor_code ? $item->item->vendor_code : '-' }}</td>
                    {{-- <td><img src="{{ '\storage' . $item->item->img }}" width="50"></td> --}}
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                    <td>{{ number_format($item->item->retail_price, 0, '.', ' ') }} ₽</td>
                    <td>{{ number_format($item->quantity * $item->item->retail_price * (1 - ($item->discount ? $item->discount : auth()->user()->discount) / 100), 0, '.', ' ') }} ₽</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Openings</h2>
    @if($order->orderOpenings->isNotEmpty())
        <ul>
            @foreach($order->orderOpenings as $opening)
                <li>{{ $opening->type }} - {{ $opening->doors }}</li>
            @endforeach
        </ul>
    @else
        <p>No openings associated with this order.</p>
    @endif

    <h2>Total</h2>
    <p><strong>Total Price:</strong> {{ number_format($order->total_price, 2, '.', ' ') }} ₽</p>

    <div class="footer">
        <p>Generated on {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
