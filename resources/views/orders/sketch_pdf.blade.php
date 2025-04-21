@php
$openings = [
    [
        'type' => 'left',
        'width' => 5000,
        'height' => 2700,
        'doors' => 3,
        "a" => 14,
        "b" => 17,
        "c" => 13,
        "d" => 12,
        "e" => 30,
        "f" => 6,
        "g" => 55,
        "i" => 550
    ],
    [
        'type' => 'left',
        'width' => 5000,
        'height' => 2700,
        'doors' => 4,
        "a" => 14,
        "b" => 17,
        "c" => 13,
        "d" => 12,
        "e" => 30,
        "f" => 6,
        "g" => 55,
        "i" => 550
    ],
    [
        'type' => 'right',
        'width' => 8000,
        'height' => 3000,
        'doors' => 8,
        "a" => 14,
        "b" => 17,
        "c" => 13,
        "d" => 12,
        "e" => 30,
        "f" => 6,
        "g" => 55,
        "i" => 550
    ],
    [
        'type' => 'center',
        'width' => 16000,
        'height' => 4000,
        'doors' => 10,
        "a" => 14,
        "b" => 17,
        "c" => 13,
        "d" => 12,
        "e" => 30,
        "f" => 6,
        "g" => 55,
        "i" => 550
    ],
    [
        'type' => 'center',
        'width' => 4500,
        'height' => 2600,
        'doors' => 4,
        "a" => 14,
        "b" => 17,
        "c" => 13,
        "d" => 12,
        "e" => 30,
        "f" => 6,
        "g" => 55,
        "i" => 550
    ],
];
$openingName = [
    'left' => 'Левый проем',
    'right' => 'Правый проем',
    'center' => 'Центральный проем',
    'inner-left' => 'Входная группа левая',
    'inner-right' => 'Входная группа правая',
    'blind-glazing' => 'Глухое остекление',
    'triangle' => 'Треугольник',
];

$glass_counter = 0;

function svgLeft($g, $i, $d, $mr) {
    $svgLeft = '<svg
        width="29"
        height="106"
        viewBox="0 0 34 86"
        xmlns="http://www.w3.org/2000/svg"
        >
          <!-- Group for graphical elements -->
          <g transform="scale(-1, 1) translate(-34, 0)">
            <!-- 1) Main vertical line -->
            <line
              x1="19.40" y1="17.14"
              x2="19.40" y2="106"
              stroke="black"
              stroke-width="0.8"
            />
        
            <!-- 2) Short horizontal line near top -->
            <line
              x1="19.40"  y1="17.14"
              x2="23.85"  y2="17.14"
              stroke="black"
              stroke-width="0.8"
            />
        
            <!-- 3) Long horizontal line across the middle -->
            <line
              x1="4.26"   y1="60"
              x2="29.11"  y2="60"
              stroke="black"
              stroke-width="0.8"
            />
        
            <!-- 4) Angled line (top-right slant) -->
            <line
              x1="23.85"  y1="17.14"
              x2="6.71"   y2="0.06"
              stroke="black"
              stroke-width="0.8"
            />
        
            <rect
              x="19.40"   y="60"
              width="6.71"  height="6.71"
              fill="none"
              stroke="black"
              stroke-width="0.8"
            />
          </g>
        
          <!-- 6) Labels (scaled down, smaller font-size) -->
        
          <!-- “1000” around mid-vertical -->
          <text
            x="-40" y="13"
            transform="rotate(-90)"
            font-family="sans-serif"
            font-size="6"
            text-anchor="start"
          >
            ' . $mr . '
          </text>
        
          <!-- “500” near lower vertical -->
          <text
            x="-90" y="13"
            transform="rotate(-90)"
            font-family="sans-serif"
            font-size="6"
            text-anchor="start"
          >
            ' . $i . '
          </text>
        
          <!-- “55” near the middle horizontal line -->
          <text
            x="20" y="58"
            font-family="sans-serif"
            font-size="6"
            text-anchor="start"
          >
            ' . $g . '
          </text>
        
          <!-- “⌀ 12” near the angled line -->
          <text
            x="6" y="16"
            transform="rotate(-45)"
            font-family="sans-serif"
            font-size="6"
          >
            ⌀ ' . $d . '
          </text>
        </svg>';
    return $svgLeft;
}

function svgRight($g, $i, $d, $mr) {
    $svgRight = '<svg
        width="29"
        height="106"
        viewBox="0 0 34 86"
        xmlns="http://www.w3.org/2000/svg"
        >
          <!-- 
            Scaled so the shape fits a 34×86 box.
            Font size is smaller so text remains legible.
          -->
        
          <!-- 1) Main vertical line (originally 95,108 → 95,523) -->
          <line
            x1="19.40" y1="17.14"
            x2="19.40" y2="106"
            stroke="black"
            stroke-width="0.8"
          />
        
          <!-- 2) Short horizontal line near top (95,108 → 123,108) -->
          <line
            x1="19.40"  y1="17.14"
            x2="23.85"  y2="17.14"
            stroke="black"
            stroke-width="0.8"
          />
        
          <!-- 3) Long horizontal line across the middle (0,340 → 156,340) -->
          <line
            x1="4.26"   y1="60"
            x2="29.11"  y2="60"
            stroke="black"
            stroke-width="0.8"
          />
        
          <!-- 4) Angled line (top-right slant) (123,108 → 15.406,0.406) -->
          <line
            x1="23.85"  y1="17.14"
            x2="6.71"   y2="0.06"
            stroke="black"
            stroke-width="0.8"
          />
        
          <rect
            x="19.40"   y="60"
            width="6.71"  height="6.71"
            fill="none"
            stroke="black"
            stroke-width="0.8"
          />
        
          <!-- 6) Labels (scaled down, smaller font-size) -->
        
          <!-- “1000” around mid-vertical -->
          <text
            x="7" y="18"
            transform="rotate(-90)"
            font-family="sans-serif"
            font-size="6"
            text-anchor="end"
          >
            ' . $mr . '
          </text>
        
          <!-- “500” near lower vertical -->
          <text
            x="-32.00" y="18"
            transform="rotate(-90)"
            font-family="sans-serif"
            font-size="6"
            text-anchor="end"
          >
            ' . $i . '
          </text>
        
          <!-- “55” near the middle horizontal line -->
          <text
            x="54" y="57"
            font-family="sans-serif"
            font-size="6"
            text-anchor="end"
          >
            ' . $g . '
          </text>
        
          <!-- “⌀ 12” near the angled line -->
          <text
            x="10" y="-7"
            transform="rotate(45)"
            font-family="sans-serif"
            font-size="6"
          >
            ⌀ ' . $d . '
          </text>
        </svg>';
    return $svgRight;
}

function getDoorHandleSVG($direction = 'right', $g = 55, $i = 550, $d = 12, $mr = 900) {
    return $direction == 'left' ? svgLeft($g, $i, $d, $mr) : svgRight($g, $i, $d, $mr);
}
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title></title>
<style>
    @page {
        margin: 10px;
    }
    .no-break {
        page-break-inside: avoid; 
        break-inside: avoid;
    }

    h1,
    h2,
    h3,
    h4 {
        font-size: 12px;
        margin: 0;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        /* margin-bottom: 10px;
        margin-top: 10px; */
        font-size: 10px !important;
    }

    table th,
    table td {
        border: 1px solid #ddd;
        padding: 4px;
        text-align: left;
        font-weight: normal;
    }

    table th {
        background-color: #f2f2f2;
    }

    .nowrap {
        white-space: nowrap;
    }

    .footer {
        text-align: center;
        margin-top: 20px;
    }

    .bold {
        font-weight: bold;
    }

    .center {
        text-align: center;
    }

    .inline-block {
        display: inline-block;
    }

    .glass-top {
        border: 1px solid rgb(0, 195, 255);
        width: 74px;
        height: 12px;
        display: inline-block;
        position: relative;
    }
    .glass-top div {
        margin-top: -1px;
        /* border:1px solid black; */
        /* display: flex; */
        width: 100%;
        height: 12px;
        /* text-align: left; */
        /* /* justify-content: space-between; */
        position: absolute;
    }
    .glass-top p {
        display: inline-block;
        position: relative;
        right: 0;
        color: #ec4949;
    }

    .inner-view .glass-top {
        width: 68px;
        height: 140px;
        position: relative;
        font-size:8px;
    }
    
    .small {
        font-size: 6px;
    }
</style>
</head>

<body>
<h2>Документ стекла</h2>
<table>
    <tbody>
        <tr>
            <td class="">№ Заявки</td>
            <td class="bold center">6-123-TK</td>
            <td class="">ТЕЛЕФОН</td>
            <td class=" center">79893639325</td>
            <td class="">ФИО</td>
            <td class=" center">Филатов В.И.</td>
            <td class="">ГОРОД</td>
            <td class=" center">Баксан</td>
        </tr>
        <tr>
            <td class="">Кол-во проемов</td>
            <td class="bold center">3</td>
            <td class="">Стекло</td>
            <td class="bold center">M1</td>
            <td class="">RAL</td>
            <td class="bold center">8024</td>
            <td class="">Кол-во стекол</td>
            <td class="bold center">30</td>
        </tr>
    </tbody>
</table>

<br>

<div>
    @for ($oindex = 0; $oindex < count($openings); $oindex++)
    @php
        //central opening
        $opening = $openings[$oindex];
        $stvorki = $opening['doors'];
        $gap = $opening['type'] == 'center' ? $opening['a'] + $opening['b'] + $opening['e'] + $opening['g'] + 3 : 130;
        $doorsGap = [
            'start' => $opening['e'] + $opening['g'],
            'end' => $opening['b'],
        ];
        
        $overlaps = $stvorki / ($opening['type'] == 'center' ? 2 : 1) - 1;
        $middle = intval(($overlaps * 13) / ($stvorki / ($opening['type'] == 'center' ? 2 : 1)));
        
        if ($opening['type'] == 'center') {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki / 2 - 2)) / 2);
        } else {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki - 2)) / 2);
        }
        
        $y = $opening['width'] / ($opening['type'] == 'center' ? 2 : 1) - $gap;
        
        $z = $y / ($stvorki / ($opening['type'] == 'center' ? 2 : 1));

        $shirinaStvorok = [];
    @endphp
    <div class="no-break">
        <table>
            <tbody>
                <tr>
                    <td class="">{{ $openingName[$opening['type']] }} на {{ $opening['doors'] }} ств.</td>
                    <td class="">Проем №</td>
                    <td class="bold center">{{ $oindex + 1 }}</td>
                    <td class="">Высота</td>
                    <td class="bold center">{{ $opening['height'] }}</td>
                    <td class="">Ширина</td>
                    <td class="bold center">{{ $opening['width'] }}</td>
                    <td>* По наименьшему размеру без учета завалов в проеме!</td>
                </tr>
        </table>
        
        <br>
    
        <div class="center" style="font-size: 10px;margin-top:12px;">
            <div style="color:#ec4949">УЛИЦА</div>
            @if ($opening['type'] == 'center')
                @for ($openingDoorsIndex = 1; $openingDoorsIndex <= $stvorki / 2; $openingDoorsIndex++)
                @php
                    $temp = $z + ($openingDoorsIndex == $stvorki / 2 || $openingDoorsIndex == 1 ? $edges : $middle);
                    
                    if ($openingDoorsIndex == $stvorki / 2) {
                        $temp += $doorsGap['start'];
                    } else if ($openingDoorsIndex == 1) {
                        $temp += $doorsGap['end'];
                    }
                    $temp2 = 0;
                    // $temp2 = ($stvorki == 4 && ($openingDoorsIndex != 1 || $openingDoorsIndex != $stvorki / 2) ? -1 : 0);
                    // if ($stvorki == 4 && ($openingDoorsIndex != 1 || $openingDoorsIndex != $stvorki / 2)) {
                    //     $temp2 = -0.5;
                    // }
                                        
                    $shirinaStvorok[$openingDoorsIndex] = intval($temp + $temp2);
                    $shirinaStvorok[$stvorki - $openingDoorsIndex + 1] = intval($temp + $temp2);
                @endphp
                <div style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                    <div class="inline-block">
                        <div>СТ{{ $glass_counter + $openingDoorsIndex }}</div>
                        <div class="glass-top">
                            <div>
                                <p class="small">
                                    @switch($openingDoorsIndex)
                                        @case(1)
                                            L19
                                            @break
                                            @case($stvorki / 2)
                                                L15
                                                @break
                                        @default
                                            L15/22
                                    @endswitch
                                </p>
                                <p class="small" style="width:calc(100% - 50px)"></p>
                                <p class="small">
                                    @switch($openingDoorsIndex)
                                        @case(1)
                                            L16/21
                                            @break
                                        @case($stvorki / 2)
                                            L17
                                            @break
                                        @default
                                            L16/21
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="inline-block" style="width: calc(6px + {{ ($stvorki / 2 - $openingDoorsIndex) * 110 }});"></div>
                    <div class="inline-block">
                        <div>СТ{{ $glass_counter + $stvorki - $openingDoorsIndex + 1 }}</div>
                        <div class="glass-top">
                            <div>
                                <p class="small">
                                    @switch($openingDoorsIndex)
                                        @case($stvorki / 2)
                                            L20
                                            @break
                                        @case(1)
                                            L19/21
                                            @break
                                        @default
                                            L19/21
                                    @endswitch
                                </p>
                                <p class="small" style="width:calc(100% - 50px)"></p>
                                <p class="small">
                                    @switch($openingDoorsIndex)
                                        @case(1)
                                            L16
                                            @break
                                        @case($stvorki / 2)
                                            L18
                                            @break
                                        @default
                                            L18/22
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
            @elseif ($opening['type'] == 'left' || $opening['type'] == 'right')
                @for ($openingDoorsIndex = $stvorki; $openingDoorsIndex >= 1; $openingDoorsIndex--)
                @php
                    $temp = $z + ($openingDoorsIndex == $stvorki || $openingDoorsIndex == 1 ? $edges : $middle);
                    
                    if ($openingDoorsIndex == $stvorki) {
                        $temp += $doorsGap['start'];
                    } else if ($openingDoorsIndex == 1) {
                        $temp += $doorsGap['end'];
                    }
                    
                    $shirinaStvorok[$openingDoorsIndex] = intval($temp);
                @endphp
                <div style="margin-left: {{ $opening['type'] == 'left' ? (($stvorki % 2) ? 0 : 55) : -55 }};display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                    @if ($stvorki / 2 > $openingDoorsIndex - 1)
                        @if ($opening['type'] == 'right')
                            <div class="inline-block" style="width: calc({{ abs(($openingDoorsIndex - 1) * 110 - intval($stvorki / 2) * 110) }});"></div>
                        @endif
                    @endif
                    
                    @if ($stvorki / 2 <= $openingDoorsIndex - 1 && $opening['type'] == 'left')
                        <div class="inline-block" style="width: calc({{ (($openingDoorsIndex - 1) - intval($stvorki / 2)) * 110 }});"></div>
                    @endif
                    <div class="inline-block">
                        @if ($opening['type'] == 'left')
                            <div>СТ{{ $glass_counter + $openingDoorsIndex }}</div>
                        @else
                            <div>СТ{{ $glass_counter + $stvorki - $openingDoorsIndex + 1 }}</div>
                        @endif
                        <div class="glass-top">
                            <div>
                                <p class="small">
                                    @if ($opening['type'] == 'left')
                                        @switch($openingDoorsIndex)
                                            @case(1)
                                                L19
                                                @break
                                            @default
                                                L19/21
                                        @endswitch
                                    @else
                                        @switch($openingDoorsIndex)
                                            @case(1)
                                                L19
                                                @break
                                            @case($stvorki)
                                                L15
                                                @break
                                            @default
                                                L15/22
                                        @endswitch
                                    @endif
                                </p>
                                <p class="small" style="width:calc(100% - 50px);height:10px;"></p>
                                <p class="small">
                                    @if ($opening['type'] == 'left')
                                        @switch($openingDoorsIndex)
                                            @case(1)
                                                L18
                                                @break
                                            @case($stvorki)
                                                L16
                                                @break
                                            @default
                                                L18/22
                                        @endswitch
                                    @else
                                        @switch($openingDoorsIndex)
                                            @case($stvorki)
                                                L16
                                                @break
                                            @default
                                                L16/21
                                        @endswitch
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @if ($stvorki / 2 <= $openingDoorsIndex - 1)
                        @if ($opening['type'] == 'right')
                            <div class="inline-block" style="width: calc({{ (($openingDoorsIndex - 1) - intval($stvorki / 2)) * 110 }});"></div>
                        @endif
                    @endif
                    
                    @if ($stvorki / 2 > $openingDoorsIndex - 1 && $opening['type'] == 'left')
                        <div class="inline-block" style="width: calc({{ abs(($openingDoorsIndex - 1) * 110 - intval($stvorki / 2) * 110) }});"></div>
                    @endif
                </div>
            @endfor
            @endif
        </div>
    
        <div class="center inner-view" style="font-size: 10px;margin-top:24px;">
            <div style="display: flex; justify-content: center;align-items:center;justify-content: center;gap:16px;margin-bottom:0px;">
                @for ($i = 1; $i <= $stvorki; $i++)
                    <div class="inline-block">
                        <div>СТ{{ $i + $glass_counter }}</div>
                        <div class="glass-top" style="position:relative">
                            @if ($opening['type'] == 'left' || $opening['type'] == 'center' && $i > $stvorki / 2)
                                <span style="position: absolute;top: 50%;right: -4px;transform: rotate(-90deg);">{{ $opening['height'] - 103 }}</span>
                            @else
                                <span style="position: absolute;top: 50%;left: -4px;transform: rotate(-90deg);">{{ $opening['height'] - 103 }}</span>
                            @endif
                            <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">{{ $shirinaStvorok[$i] }}</span>
                            
                            @if ($opening['type'] == 'left' && $i == 1 || $opening['type'] == 'right' && $i == $stvorki || $opening['type'] == 'center' && ($i - $stvorki / 2) <= 1 && ($i - $stvorki / 2) >= 0)
                                @if ($opening['type'] == 'left' || $opening['type'] == 'center' && $i > $stvorki / 2)
                                    <img src="data:image/jpeg;base64,{{ base64_encode(getDoorHandleSVG('left')) }}" alt="" height="86" style="position: absolute;bottom:0;left:-4px;">
                                @else
                                    <img src="data:image/jpeg;base64,{{ base64_encode(getDoorHandleSVG('right')) }}" alt="" height="86" style="position: absolute;bottom:0;right:0;">
                                @endif
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    @php
        $glass_counter += $stvorki
    @endphp
    @endfor
</div>
</body>
</html>