<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Коммерческое предложение</title>
    <style>
    @page {
        margin:10px;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        margin: 0;
        padding: 0;
        font-size:12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        margin-top: 10px;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 4px;
        text-align: left;
        font-weight: normal;
    }
    .table th, .table td {
        text-align: center;
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
    {{-- <p>{{ now()->format('d.m.Y') }}</p> --}}
    
    <table>
        <thead>
            <tr>
                <th>Информация о клиенте</th>
                <th>{{ $offer['manufacturer']['title'] }}</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Клиент: <b>{{ $offer['customer']['name'] }}</b></td>
                <td>Производитель: <b>{{ $offer['manufacturer']['manufacturer'] }}</b></td>
            </tr>
            <tr>
                <td>Адрес: <b>{{ $offer['customer']['address'] }}</b></td>
                <td>Телефон: <b>{{ $offer['manufacturer']['phone'] }}</b></td>
            </tr>
            <tr>
                <td>Телефон: <b>{{ $offer['customer']['phone'] }}</b></td>
                <td></td>
            </tr>
            @if ($offer['customer']['comment'])
                <tr>
                    <td colspan="2"><b>Примечание:</b> {{ $offer['customer']['comment'] }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- <h2>Проемы</h2> --}}
    <table class="table">
        <thead>
            <tr>
                <th>Картинка</th>
                <th>Тип проема</th>
                <th>Кол-во створок</th>
                <th>Ш х В</th>
                <th>Площадь</th>
            </tr>
        </thead>
        <tbody>
            @php
                $markupPercentage = $offer['markup_percentage'] / 100 + 1;
                
                $openingImage = [
                    'left' => '/assets/openings/openings-left.jpg',
                    'right' => '/assets/openings/openings-right.jpg',
                    'center' => '/assets/openings/openings-center.jpg',
                    'inner-left' => '/assets/openings/openings-inner-left.jpg',
                    'inner-right' => '/assets/openings/openings-inner-right.jpg',
                    'blind-glazing' => '/assets/openings/openings-blind-glazing.jpg',
                    'triangle' => '/assets/openings/openings-triangle.jpg',
                ]
            @endphp
            @foreach($offer['openings'] as $opening)
                <tr>
                    <td>
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public' . $openingImage[$opening['type']]))) }}" alt="" width="86">
                    </td>
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
                    <td>{{ $opening['width'] }}мм x {{ $opening['height'] }}мм</td>
                    <td>{{ ($opening['width'] * $opening['height']) / 1000000 }}м<sup>2</sup></td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" style="text-align: right">Сумма: <b>{{ number_format($offer_openings_price * $markupPercentage, 0, '.', ' ') }} ₽</b></td>
            </tr>
        </tbody>
    </table>
    
    {{-- {{ $additional_items }} --}}

    {{-- <h2>Доп. детали</h2> --}}
    <table class="table">
        <thead>
            <tr>
                <th>№</th>
                <th>Картинка</th>
                <th style="text-align: left !important">Наименование</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Итого</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 0;
            @endphp
            @foreach ($offer['additional_items'] as $item)
                @if (isset($offer['cart_items'][$item['id']]))
                    @php
                        $price = App\Models\Item::itemPrice($item['id']) * $markupPercentage;
                        $quantity = $offer['cart_items'][$item['id']]['quantity'];
                        $total = $price * $quantity;
                        // $sum += $total;
                    @endphp
                    <tr>
                        <td>{{ ++$count }}</td>
                        <td>
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($item['img'][0] != '/' ? '/' : '') . $item['img']))) }}" alt="" width="48">
                        </td>
                        <td style="text-align: left !important">@if ($item['vendor_code']) {{ $item['vendor_code'] . ' - ' }} @endif {{ $item['name'] }}</td>
                        <td class="nowrap">{{ $quantity }} {{ $item['unit'] }}</td>
                        <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                        <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                    </tr>
                @endif
            @endforeach

            @foreach ($offer['services'] as $service)
                @if (isset($offer['cart_items'][$service['id']]))
                    @php
                        $price = App\Models\Item::itemPrice($service['id']) * $markupPercentage;
                        $quantity = $offer['cart_items'][$service['id']]['quantity'];
                        $total = $price * $quantity;
                    @endphp
                    <tr>
                        <td>{{ ++$count }}</td>
                        <td>
                            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($service['img'][0] != '/' ? '/' : '') . $service['img']))) }}" alt="" width="48">
                        </td>
                        <td style="text-align: left !important">@if ($service['vendor_code']) {{ $service['vendor_code'] . ' - ' }} @endif {{ $service['name'] }}</td>
                        <td class="nowrap">{{ $quantity }} {{ $service['unit'] }}</td>
                        <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                        <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                    </tr>
                @endif
            @endforeach

            @if (isset($offer['cart_items'][$offer['glass']['id']]))
                @php
                    $price = App\Models\Item::itemPrice($offer['glass']['id']) * $markupPercentage;
                    $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
                    $total = $price * $quantity;
                @endphp
                <tr>
                    <td>{{ ++$count }}</td>
                    <td>
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($offer['glass']['img'][0] != '/' ? '/' : '') . $offer['glass']['img']))) }}" alt="" width="48">
                    </td>
                    <td style="text-align: left !important">@if ($offer['glass']['vendor_code']) {{ $offer['glass']['vendor_code'] . ' - ' }} @endif {{ $offer['glass']['name'] }}</td>
                    <td class="nowrap">{{ $quantity }} {{ $offer['glass']['unit'] }}</td>
                    <td class="nowrap">{{ number_format($price, 0, '.', ' ') }} ₽</td>
                    <td class="nowrap">{{ number_format($total, 0, '.', ' ') }} ₽</td>
                </tr>
            @endif

            <tr>
                <td colspan="5" style="text-align: right">Итого:</td>
                <td class="nowrap"><b>{{ number_format($offer['total_price'] * $markupPercentage, 0, '.', ' ') }} ₽</b></td>
            </tr>
        </tbody>
    </table>
</body>
</html>