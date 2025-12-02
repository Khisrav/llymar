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

function getLogoPath($user) {
    // If user has a logo, use it
    if ($user && $user->logo) {
        $logoPath = storage_path('app/public/' . $user->logo);
        if (file_exists($logoPath)) {
            return $logoPath;
        }
    }
    
    // Fall back to default LLYMAR logo
    return base_path('public/assets/logo.jpg');
}

function getLogoBase64($user) {
    $logoPath = getLogoPath($user);
    if (file_exists($logoPath)) {
        return base64_encode(file_get_contents($logoPath));
    }
    return '';
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
    <table style="margin:0">
        <tbody>
            <tr>
                <td style="padding:0;width:50%">
                    <div>
                        <h1 style="margin:0;padding:0;font-size:16px !important;">Производство и поставка систем<br> безрамного остекления LLYMAR</h1>
                        {{-- <p style="margin:0;padding:0;font-size:14px !important;line-height:15px;">на поставку безрамной системы остекления LLYMAR</p> --}}
                    </div>
                </td>
                <td style="text-align: center;padding:0;width:50%">
                    <img src="data:image/jpeg;base64,{{ getLogoBase64($user ?? null) }}" alt="" style="height:48px">
                </td>
            </tr>
        </tbody>
    </table>
    <table style="table-layout: fixed;border:none;margin-top:0;" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border:none;vertical-align: top">
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
                                $price = App\Models\Item::itemPrice($offer['glass']['id'], $selected_factor ?? 'pz') * $markupPercentage;
                                $quantity = $offer['cart_items'][$offer['glass']['id']]['quantity'];
                                $total = $price * $quantity;
                            @endphp
                            <tr>
                                <td>{{ ++$count }}</td>
                                <td style="text-align: left !important">{{ $offer['glass']['name'] }}</td>
                                <td>
                                    <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($offer['glass']['img'][0] != '/' ? '/' : '') . $offer['glass']['img']))) }}" alt="" width="40">
                                </td>
                                <td class="nowrap">{{ rtrim(rtrim(number_format($quantity, 2, '.', ' '), '0'), '.') }} {{ $offer['glass']['unit'] }}</td>
                            </tr>
                        @endif
                        
                        @foreach ($offer['additional_items'] as $item)
                            @if (isset($offer['cart_items'][$item['id']]))
                                @php
                                    $price = App\Models\Item::itemPrice($item['id'], $selected_factor ?? 'pz') * $markupPercentage;
                                    $quantity = $offer['cart_items'][$item['id']]['quantity'];
                                    $total = $price * $quantity;
                                @endphp
                                <tr>
                                    <td>{{ ++$count }}</td>
                                    <td style="text-align: left !important">@if ($item['vendor_code']) {{ $item['vendor_code'] . ' - ' }} @endif {{ $item['name'] }}</td>
                                    <td>
                                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($item['img'][0] != '/' ? '/' : '') . $item['img']))) }}" alt="" width="40">
                                    </td>
                                    <td class="nowrap">{{ rtrim(rtrim(number_format($quantity, 2, '.', ' '), '0'), '.') }} {{ $item['unit'] }}</td>
                                </tr>
                            @endif
                        @endforeach
            
                        @foreach ($offer['services'] as $service)
                            @if (isset($offer['cart_items'][$service['id']]))
                                @php
                                    $price = App\Models\Item::itemPrice($service['id'], $selected_factor ?? 'pz') * $markupPercentage;
                                    $quantity = $offer['cart_items'][$service['id']]['quantity'];
                                    $total = $price * $quantity;
                                @endphp
                                <tr>
                                    <td>{{ ++$count }}</td>
                                    <td style="text-align: left !important">@if ($service['vendor_code']) {{ $service['vendor_code'] . ' - ' }} @endif {{ $service['name'] }}</td>
                                    <td>
                                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($service['img'][0] != '/' ? '/' : '') . $service['img']))) }}" alt="" width="40">
                                    </td>
                                    <td class="nowrap">{{ rtrim(rtrim(number_format($quantity, 2, '.', ' '), '0'), '.') }} {{ $service['unit'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                
                @php
                    // Calculate base price (total without any glass)
                    $basePrice = $offer['total_price'] * $markupPercentage;
                    $regularGlassPrice = 0;
                    
                    if ($offer['glass'] && isset($offer['cart_items'][$offer['glass']['id']])) {
                        $glassItem = $offer['glass'];
                        $glassPrice = App\Models\Item::itemPrice($glassItem['id'], $selected_factor ?? 'pz') * $markupPercentage;
                        $glassQuantity = $offer['cart_items'][$glassItem['id']]['quantity'];
                        $regularGlassPrice = $glassPrice * $glassQuantity;
                        $basePrice = $basePrice - $regularGlassPrice;
                    }
                    
                    // Prepare array of totals
                    $totals = [];
                    
                    // Total with regular glass
                    if ($offer['glass'] && isset($offer['cart_items'][$offer['glass']['id']])) {
                        $totals[] = [
                            'label' => 'Итого с ' . $offer['glass']['vendor_code'],
                            'price' => $basePrice + $regularGlassPrice
                        ];
                    }
                @endphp
                
                @if (count($totals) > 1)
                    @foreach ($totals as $index => $totalInfo)
                        <p style="text-align: right;font-size:13px;margin:2px 0;">{{ $totalInfo['label'] }}: {{ number_format($totalInfo['price'], 0, '.', ' ') }} ₽</p>
                    @endforeach
                @else
                    <p style="text-align: right;font-size:13px;">Итого: {{ number_format($offer['total_price'] * $markupPercentage, 0, '.', ' ') }} ₽</p>
                @endif
                
                @if (isset($offer['ghost_glasses']) && is_array($offer['ghost_glasses']) && count($offer['ghost_glasses']) > 0)
                    <table class="table" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th style="text-align: left !important">Альтернативный вариант стекла</th>
                                <th>Итого</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offer['ghost_glasses'] as $ghost_glass)
                                @if (isset($offer['cart_items'][$ghost_glass['id']]))
                                    @php
                                        $ghostGlassPrice = App\Models\Item::itemPrice($ghost_glass['id'], $selected_factor ?? 'pz') * $markupPercentage;
                                        $ghostGlassQuantity = $offer['cart_items'][$ghost_glass['id']]['quantity'];
                                        $totalWithGhostGlass = $basePrice + ($ghostGlassPrice * $ghostGlassQuantity);
                                    @endphp
                                    <tr>
                                        <td style="text-align: left !important">Если {{ $ghost_glass['name'] }}</td>
                                        <td>{{ number_format($totalWithGhostGlass, 0, '.', ' ') }} ₽</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </td>
            <td style="vertical-align: top;border:none">
                <table>
                    <thead>
                        <tr>
                            <th>Информация о клиенте</th>
                            <th>{{ $offer['manufacturer']['title'] ?? 'Информация о производителе' }}</th>
                        </tr>
                    </thead>
            
                    <tbody>
                        <tr>
                            <td><b>{{ $offer['customer']['name'] }}</b></td>
                            <td><b>{{ $offer['manufacturer']['manufacturer'] ?? '-' }}</b></td>
                        </tr>
                        <tr>
                            <td><b>{{ $offer['customer']['address'] }}</b></td>
                            <td><b>{{ $offer['manufacturer']['phone'] ?? '-' }}</b></td>
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