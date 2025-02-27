<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Перечень</title>
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
            padding: 4px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .nowrap {
            white-space: nowrap;
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
        @if ($order->order_number)
            <h1>Заказ №{{ $order->order_number }}</h1>
        @else
            <h1>Перечень</h1>
        @endif
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
                <td><b>Комментарий к заказу:</b> {{ $order->comment ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Проемы</h2>
    @if($orderOpenings)
        <table class="table">
            <thead>
                <tr>
                    <th>Тип проема</th>
                    <th>Кол-во створок</th>
                    <th>Ш х В, мм</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderOpenings as $opening)
                    <tr>
                        <td>
                            @switch($opening->type)
                                @case('left')
                                    Левый проем
                                    @break
                                @case('right')
                                    Правый проем
                                    @break
                                @case('center')
                                    Центральный проем
                                    @break
                                @case('inner-left')
                                    Входная группа левая
                                    @break
                                @case('inner-right')
                                    Входная группа правая
                                    @break
                                @case('blind-glazing')
                                    Глухое остекление
                                    @break
                                @case('triangle')
                                    Треугольник
                                    @break
                                @default
                                    {{ $opening->type }}
                            @endswitch
                        </td>
                        <td>{{ $opening->type != 'blind-glazing' && $opening->type != 'triangle' ? $opening->doors . ' ств.' : '-' }}</td>
                        <td>{{ $opening->width }} x {{ $opening->height }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Детали</h2>
    <table class="table">
        <thead>
            <tr>
                <th>№</th>
                <th>Картинка</th>
                <th>Деталь</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 0;
            @endphp
            @foreach($orderItems as $item)
                <tr>
                    <td>{{ ++$count }}</td>
                    <td>
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($item->item->img[0] != '/' ? '/' : '') . $item->item->img))) }}" alt="" width="48">
                    </td>
                    <td>{{ $item->item->name . ($item->item->vendor_code ? ' - ' . $item->item->vendor_code : '') }}</td>
                    <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                    <td>{{ number_format($item->itemTotalPrice / $item->quantity, 0, '.', ' ') }} ₽</td>
                    <td>{{ number_format($item->itemTotalPrice, 0, '.', ' ') }} ₽</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><b>Сумма:</b></td>
                <td><b>{{ number_format($order->total_price, 0, '.', ' ') }} ₽</b></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Дата генерации файла: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
