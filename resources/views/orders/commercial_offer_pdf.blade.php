<?php
function groupArraysByProperties($arrays, $properties) {
    $grouped = array();
    $indexedGrouped = array();

    foreach ($arrays as $array) {
        $key = array();
        foreach ($properties as $property) {
            if (isset($array[$property])) {
                $key[] = $array[$property];
            }
        }
        $key = implode('-', $key);

        if (!isset($grouped[$key])) {
            $grouped[$key] = array();
        }
        $grouped[$key][] = $array;
    }

    foreach ($grouped as $group) {
        $indexedGrouped[] = $group;
    }

    return $indexedGrouped;
}
?>
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
        font-size: 10px;
    }
    .footer {
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>
<body>
    <table style="table-layout: fixed;border:none;margin-top:0;" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:none;vertical-align: top">
                <table style="margin-top:0;">
                    <tbody>
                        <tr>
                            <td style="border:none">
                                <div>
                                    <h1 style="margin:0;padding:0;font-size:16px !important;">Коммерческое предложение</h1>
                                    <p style="margin:0;padding:0;font-size:14px !important;line-height:15px;">на поставку безрамной системы остекления</p>
                                </div>
                            </td>
                            <td style="text-align: right;border:none">
                                {{-- <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/assets/logo.jpg'))) }}" alt="" style="height:30px;width:auto"> --}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            
                <table class="table">
                    <thead>
                        <tr>
                            <th>Открытие</th>
                            <th>Кол-во<br>створок</th>
                            <th>Ш х В<br>в мм</th>
                            <th>Площадь</th>
                            <th>Кол-во<br>проемов</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $markupPercentage = $offer['markup_percentage'] / 100 + 1;
                            
                            // $openingImage = [
                            //     'left' => '/assets/openings/openings-left.jpg',
                            //     'right' => '/assets/openings/openings-right.jpg',
                            //     'center' => '/assets/openings/openings-center.jpg',
                            //     'inner-left' => '/assets/openings/openings-inner-left.jpg',
                            //     'inner-right' => '/assets/openings/openings-inner-right.jpg',
                            //     'blind-glazing' => '/assets/openings/openings-blind-glazing.jpg',
                            //     'triangle' => '/assets/openings/openings-triangle.jpg',
                            // ];
                            
                            $groupedOpenings = groupArraysByProperties($offer['openings'], ['doors', 'width', 'height', 'type']);
                        @endphp
                        @foreach($groupedOpenings as $opening)
                            <tr>
                                <td>
                                    {{-- put opening name by type --}}
                                    @switch($opening[0]['type'])
                                        @case('left')
                                            Левое
                                            @break
                                        @case('right')
                                            Правое
                                            @break
                                        @case('center')
                                            Центральное
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
                                            -
                                    @endswitch
                                    {{-- <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public' . $openingImage[$opening[0]['type']]))) }}" alt="" width="86"> --}}
                                </td>
                                <td>{{ $opening[0]['type'] != 'blind-glazing' && $opening[0]['type'] != 'triangle' ? $opening[0]['doors'] . ' ств.' : '-' }}</td>
                                <td>{{ $opening[0]['width'] }}x{{ $opening[0]['height'] }}</td>
                                <td>{{ number_format(($opening[0]['width'] * $opening[0]['height']) / 1000000, 2, '.', ' ') }}м<sup>2</sup></td>
                                <td>{{ count($opening) }} шт.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th style="text-align: left !important">Наименование</th>
                            <th></th>
                            <th>Кол-во</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $count = 1;
                        @endphp
                        <tr>
                            <td>{{ $count }}</td>
                            <td style="text-align: left !important">Система безрамного остекления LLYMAR</td>
                            <td></td>
                            <td class="nowrap">{{ count($offer['openings']) }} шт.</td>
                        </tr>
            
                        @if ($offer['glass'] && isset($offer['cart_items'][$offer['glass']['id']]))
                            @php
                                $price = App\Models\Item::itemPrice($offer['glass']['id'], $selected_factor ?? 'kz') * $markupPercentage;
                                $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
                                $total = $price * $quantity;
                            @endphp
                            <tr>
                                <td>{{ ++$count }}</td>
                                <td style="text-align: left !important">@if ($offer['glass']['vendor_code']) {{ $offer['glass']['vendor_code'] . ' - ' }} @endif {{ $offer['glass']['name'] }}</td>
                                <td>
                                    <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($offer['glass']['img'][0] != '/' ? '/' : '') . $offer['glass']['img']))) }}" alt="" width="40">
                                </td>
                                <td class="nowrap">{{ number_format($quantity, 2, '.', ' ') }} {{ $offer['glass']['unit'] }}</td>
                            </tr>
                        @endif
                        
                        @foreach ($offer['additional_items'] as $item)
                            @if (isset($offer['cart_items'][$item['id']]))
                                @php
                                    $price = App\Models\Item::itemPrice($item['id'], $selected_factor ?? 'kz') * $markupPercentage;
                                    $quantity = $offer['cart_items'][$item['id']]['quantity'];
                                    $total = $price * $quantity;
                                @endphp
                                <tr>
                                    <td>{{ ++$count }}</td>
                                    <td style="text-align: left !important">@if ($item['vendor_code']) {{ $item['vendor_code'] . ' - ' }} @endif {{ $item['name'] }}</td>
                                    <td>
                                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($item['img'][0] != '/' ? '/' : '') . $item['img']))) }}" alt="" width="40">
                                    </td>
                                    <td class="nowrap">{{ number_format($quantity, 2, '.', ' ') }} {{ $item['unit'] }}</td>
                                </tr>
                            @endif
                        @endforeach
            
                        @foreach ($offer['services'] as $service)
                            @if (isset($offer['cart_items'][$service['id']]))
                                @php
                                    $price = App\Models\Item::itemPrice($service['id'], $selected_factor ?? 'kz') * $markupPercentage;
                                    $quantity = $offer['cart_items'][$service['id']]['quantity'];
                                    $total = $price * $quantity;
                                @endphp
                                <tr>
                                    <td>{{ ++$count }}</td>
                                    <td style="text-align: left !important">@if ($service['vendor_code']) {{ $service['vendor_code'] . ' - ' }} @endif {{ $service['name'] }}</td>
                                    <td>
                                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($service['img'][0] != '/' ? '/' : '') . $service['img']))) }}" alt="" width="40">
                                    </td>
                                    <td class="nowrap">{{ number_format($quantity, 2, '.', ' ') }} {{ $service['unit'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <p style="text-align: right;font-size:14px;">Итого ({{ strtoupper($selected_factor ?? 'kz') }}): {{ number_format($offer['total_price'] * $markupPercentage, 0, '.', ' ') }} ₽</p>
            </td>
            <td style="vertical-align: top;border:none">
                <table>
                    <tbody>
                        <tr>
                            <td style="border:none">
                                {{-- <div style='font-size:14px;font-weight:semibold;'>
                                    <h1 style="margin:0;padding:0;">Коммерческое предложение</h1>
                                    <p style="margin:0;padding:0;">на поставку безрамной системы остекления</p>
                                </div> --}}
                            </td>
                            <td style="text-align: right;border:none">
                                <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/assets/logo.jpg'))) }}" alt="" style="height:34px;width:auto">
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <table>
                    <thead>
                        <tr>
                            <th>Информация о клиенте</th>
                            <th>{{ $offer['manufacturer']['title'] }}</th>
                        </tr>
                    </thead>
            
                    <tbody>
                        <tr>
                            <td><b>{{ $offer['customer']['name'] }}</b></td>
                            <td><b>{{ $offer['manufacturer']['manufacturer'] }}</b></td>
                        </tr>
                        <tr>
                            <td><b>{{ $offer['customer']['address'] }}</b></td>
                            <td><b>{{ $offer['manufacturer']['phone'] }}</b></td>
                        </tr>
                        <tr>
                            <td><b>{{ $offer['customer']['phone'] }}</b></td>
                            <td></td>
                        </tr>
                        @if ($offer['customer']['comment'])
                            <tr>
                                <td colspan="2">{{ $offer['customer']['comment'] }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                
                <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/assets/commercial-offer-hero.jpg'))) }}" alt="" width="100%">
            </td>
        </tr>
    </table>
</body>
</html>