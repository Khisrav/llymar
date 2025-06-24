<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Спецификация</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: -16px;
            padding: 0px;
        }
        
        /* Page numbering */
        @page {
            margin: 100px 25px 60px 25px;
        }
        
        @page:first {
            margin: 25px 25px 60px 25px;
        }
        
        .page-number {
            position: fixed;
            bottom: 20px;
            right: 25px;
            font-size: 10px;
            color: #666;
        }
        
        /* .page-number:before {
            content: "" counter(page) " из " counter(pages);
        } */
        
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
            margin-bottom: 8px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            text-align: center;
            font-size: 10px !important;
            line-height: 10px;
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
            font-size: 11px;
        }
        
        /* Profile grid styles */
        .profiles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .profile-cell {
            width: 16.66%; /* 100% / 6 columns */
            height: 120px;
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            vertical-align: top;
            position: relative;
            background-color: white;
        }
        
        .profile-cell.unchecked {
            background-color: #f5f5f5;
            color: #888;
        }
        
        .profile-diagram {
            /* height: 40px; */
            margin-bottom: 4px;
            display: block;
        }
        
        .profile-name {
            font-size: 10px;
            line-height: 10px;
            margin-bottom: 4px;
        }
        
        .profile-quantity {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .profile-checkbox {
            /* position: absolute; */
            bottom: 3px;
            left: 3px;
            width: 8px;
            height: 8px;
            border: 1px solid #000;
            margin-bottom: 2px;
        }
        
        .unchecked-overlay {
            /* position: absolute; */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            font-weight: bold;
            color: #666;
        }
        
        .note {
            font-size: 9px;
            text-align: center;
            margin-top: 12px;
            font-style: italic;
        }
        
        .highlighted-opening {
            background-color: #ffeb3b !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table style="width: 100%;font-size: 10px;">
        <tbody>
            <tr>
                <td style="">
                    <div class="" style="text-align: left">
                        @if ($order->order_number)
                            <h1>Заказ №{{ $order->order_number }}</h1>
                        @else
                            <h1>Спецификация</h1>
                        @endif
                    </div>
                </td>
                <td style="text-align: right">
                    <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/assets/logo.jpg'))) }}" alt="" style="height:20px;width:auto">
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-bottom: 8px;">
        <tbody>
            <tr>
                <td><b>ФИО:</b> {{ $order->customer_name ?? '' }}</td>
                <td><b>Тел.:</b> {{ $order->customer_phone ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2"><b>Комментарий:</b> {{ $order->comment ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    @if($orderOpenings)
        <table class="table">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Тип проема</th>
                    <th>Кол-во створок</th>
                    <th>Ш х В, мм</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $count = 0;
                @endphp
                @foreach($orderOpenings as $index => $opening)
                    <tr>
                        <td class="nowrap">{{ ++$count }}</td>
                        <td style="text-align: left !important;">
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

    <table class="profiles-table">
        @php
            $itemsPerRow = 6;
            $totalItems = count($orderItems);
            $itemsWithPlaceholders = $orderItems;
            
            // Add placeholder items to fill the grid
            $placeholdersNeeded = 24 - $totalItems;
            for($i = 0; $i < $placeholdersNeeded; $i++) {
                $itemsWithPlaceholders[] = null;
            }
            
            $rows = array_chunk($itemsWithPlaceholders, $itemsPerRow);
        @endphp
        
        @foreach($rows as $row)
            <tr>
                @foreach($row as $item)
                    <td class="profile-cell {{ $item === null || !($item->is_checked ?? true) ? 'unchecked' : '' }}">
                        <div class="profile-checkbox">
                            @if($item === null || !($item->is_checked ?? true))
                                <div class="unchecked-overlay">X</div>
                            @endif
                        </div>
                        
                        <div class="profile-diagram">
                            @if ($item !== null && $item->item->img != null)
                                <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(base_path('public/storage' . ($item->item->img[0] != '/' ? '/' : '') . $item->item->img))) }}" alt="" style="max-height: 42px;max-width:100%">
                            @endif
                        </div>
                        
                        <div class="profile-name">
                            @if($item !== null)
                                @if($item->item->vendor_code)
                                    {{ $item->item->vendor_code }}
                                @endif - {{ $item->item->name }}
                            @else
                                -
                            @endif
                        </div>
                        
                        <div class="profile-quantity">
                            @if($item !== null)
                                {{ number_format((float)$item->quantity, 0) }}{{ $item->item->unit ?? 'шт' }} ({{ in_array($item->item->vendor_code, array('L1', 'L2', 'L3', 'L4', 'L5', 'L6', 'L7', 'L8', 'L9')) ? intval($item->quantity / 6 + 0.999) . 'шт' : '-' }})
                            @endif
                        </div>                        
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <div class="note">
        *серым цветом указаны позиции которые не нужно собирать
    </div>
    
    <div class="page-number"></div>
</body>
</html>
