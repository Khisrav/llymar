<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Коммерческое предложение</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        margin: 0;
        padding: 0;
        font-size:12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        margin-top: 10px;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-weight: normal;
    }
    table th {
        background-color: #f2f2f2;
    }
    .nowrap {
        white-space: nowrap;
    }
    * {
        font-size: 12px;
    }
    .footer {
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>
<body>
    <div style='font-size:14px;font-weight:semibold;text-align:center;margin-bottom:12px'>
        <h1 style="margin:0;padding:0;">Коммерческое предложение</h1>
        <p style="margin:0;padding:0;">на поставку безрамной системы остекления</p>    
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Информация о клиенте</th>
                <th>Информация о производителе</th>
            </tr>
        </thead>
        
        <tbody>
            <tr>
                <td>Клиент: </td>
                <td>Производитель: </td>
            </tr>
            <tr>
                <td>Телефон: </td>
                <td>Организация: </td>
            </tr>
            <tr>
                <td>Адрес: </td>
                <td>Телефон: </td>
            </tr>
        </tbody>
    </table>
    
    <h2>Проемы</h2>
    <table>
        <thead>
            <tr>
                <th>Тип проема</th>
                <th>Кол-во створок</th>
                <th>Ш х В, мм</th>
            </tr>
        </thead>
        <tbody>
            @foreach($offer['openings'] as $opening)
                <tr>
                    <td>
                        @switch($opening['type'])
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
                                {{ $opening['type'] }}
                        @endswitch
                    </td>
                    <td>{{ $opening['type'] != 'blind-glazing' && $opening['type'] != 'triangle' ? $opening['doors'] . ' ств.' : '-' }}</td>
                    <td>{{ $opening['width'] }} x {{ $opening['height'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Доп. детали</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Наименование</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sum = 0;
            @endphp
            @foreach ($offer['additional_items'] as $item)
                @if (isset($offer['cart_items'][$item['id']]))
                    @php
                        $price = App\Models\Item::itemPrice($item['id']);
                        $quantity = $offer['cart_items'][$item['id']]['quantity'];
                        $total = $price * $quantity;
                        $sum += $total;
                    @endphp
                    <tr>
                        <td>{{ $item['id'] }}</td>
                        <td>@if ($item['vendor_code']) {{ $item['vendor_code'] . ' - ' }} @endif {{ $item['name'] }}</td>
                        <td class="nowrap">{{ $quantity }} {{ $item['unit'] }}</td>
                        <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                        <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                    </tr>
                @endif
            @endforeach

            @foreach ($offer['services'] as $service)
                @if (isset($offer['cart_items'][$service['id']]))
                    @php
                        $price = App\Models\Item::itemPrice($service['id']);
                        $quantity = $offer['cart_items'][$service['id']]['quantity'];
                        $total = $price * $quantity;
                        $sum += $total;
                    @endphp
                    <tr>
                        <td>{{ $service['id'] }}</td>
                        <td>@if ($service['vendor_code']) {{ $service['vendor_code'] . ' - ' }} @endif {{ $service['name'] }}</td>
                        <td class="nowrap">{{ $quantity }} {{ $service['unit'] }}</td>
                        <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                        <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                    </tr>
                @endif
            @endforeach

            @if (isset($offer['cart_items'][$offer['glass']['id']]))
                @php
                    $price = App\Models\Item::itemPrice($offer['glass']['id']);
                    $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
                    $total = $price * $quantity;
                    $sum += $total;
                @endphp
                <tr>
                    <td>{{ $offer['glass']['id'] }}</td>
                    <td>@if ($offer['glass']['vendor_code']) {{ $offer['glass']['vendor_code'] . ' - ' }} @endif {{ $offer['glass']['name'] }}</td>
                    <td class="nowrap">{{ $quantity }} {{ $offer['glass']['unit'] }}</td>
                    <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                    <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                </tr>
            @endif

            {{-- <tr>
                <td colspan="4" style="text-align: right"><b>Сумма:</b></td>
                <td><b>{{ number_format($sum, 0, '.', ' ') }} ₽</b></td>
            </tr> --}}
        </tbody>
    </table>
    
    <div class="footer">
        <p>Дата генерации файла: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
