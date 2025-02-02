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
            width: 86px;
            height: 8px;
            display: inline-block;
            position: relative;
        }

        .inner-view .glass-top {
            width: 68px;
            height: 140px;
            position: relative;
            font-size:8px;
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
        @foreach ([1,2,3] as $key => $value)
        <table>
            <tbody>
                <tr>
                    <td class="">Открывание правый 2 стекла</td>
                    <td class="">Проем №</td>
                    <td class="bold center">1</td>
                    <td class="">Высота</td>
                    <td class="bold center">3000</td>
                    <td class="">Ширина</td>
                    <td class="bold center">1500</td>
                    <td>* По наименьшему размеру без учета завалов в проеме!</td>
                </tr>
        </table>
        
        <br>

        <div class="center" style="font-size: 10px;margin-top:6px;">
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                <div class="inline-block">
                    <div>Стекло 5</div>
                    <div class="glass-top"></div>
                </div>
                <div class="inline-block" style="width: 6px;"></div>
                <div class="inline-block">
                    <div>Стекло 6</div>
                    <div class="glass-top"></div>
                </div>
            </div>
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                <div class="inline-block">
                    <div>Стекло 4</div>
                    <div class="glass-top"></div>
                </div>
                <div class="inline-block" style="width: calc(6px + 144px);"></div>
                <div class="inline-block">
                    <div>Стекло 7</div>
                    <div class="glass-top"></div>
                </div>
            </div>
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                <div class="inline-block">
                    <div>Стекло 3</div>
                    <div class="glass-top"></div>
                </div>
                <div class="inline-block" style="width: calc(6px + 288px);"></div>
                <div class="inline-block">
                    <div>Стекло 8</div>
                    <div class="glass-top"></div>
                </div>
            </div>
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-top:-16px;">
                <div class="inline-block">
                    <div>Стекло 2</div>
                    <div class="glass-top"></div>
                </div>
                <div class="inline-block" style="width: calc(6px + 432px);"></div>
                <div class="inline-block">
                    <div>Стекло 9</div>
                    <div class="glass-top"></div>
                </div>
            </div>
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: space-between;margin-top:-16px;width:100%;">
                <div class="inline-block">
                    <div>Стекло 1</div>
                    <div class="glass-top"></div>
                </div>
                <div class="inline-block" style="width: calc(6px + 576px);"></div>
                <div class="inline-block">
                    <div>Стекло 10</div>
                    <div class="glass-top"></div>
                </div>
            </div>
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;margin-bottom:0px;width:100%;">
                <div class="inline-block" style="margin-top:calc(-5 * 10px)">ВИД ИЗНУТРИ</div>
            </div>
    
        </div>
    
        <br>
    
        <div class="center inner-view" style="font-size: 10px;">
            <div
                style="display: flex; justify-content: center;align-items:center;justify-content: center;gap:16px;margin-bottom:0px;">
                <div class="inline-block">
                    <div>Стекло 1</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 2</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 3</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 4</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 5</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
    
                        @php
                        $svg = '<svg
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
        x="-3" y="18"
        transform="rotate(-90)"
        font-family="sans-serif"
        font-size="6"
        text-anchor="end"
      >
        1000
      </text>
    
      <!-- “500” near lower vertical -->
      <text
        x="-50.00" y="18"
        transform="rotate(-90)"
        font-family="sans-serif"
        font-size="6"
        text-anchor="end"
      >
        500
      </text>
    
      <!-- “55” near the middle horizontal line -->
      <text
        x="39.00" y="57"
        font-family="sans-serif"
        font-size="6"
        text-anchor="end"
      >
        55
      </text>
    
      <!-- “⌀ 12” near the angled line -->
      <text
        x="10" y="-7"
        transform="rotate(45)"
        font-family="sans-serif"
        font-size="6"
      >
        ⌀ 12
      </text>
    </svg>
    ';
                        @endphp
                        <img src="data:image/jpeg;base64,{{ base64_encode($svg) }}" style="position: absolute;bottom:0;right:0;width:26px;height:90px">
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 6</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 7</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 8</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 9</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
                <div class="inline-block">
                    <div>Стекло 10</div>
                    <div class="glass-top" style="">
                        <span style="position: absolute;top: 50%;left: -10px;transform: rotate(-90deg);">4000мм</span>
                        <span style="position: absolute;top:0;left: 50%;transform: translateX(-50%);">2700мм</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</body>

</html>