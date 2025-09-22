<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemProperty;
use App\Models\Order;
use App\Models\OrderOpening;
use DXFighter\DXFighter;
use DXFighter\lib\Circle;
use DXFighter\lib\Polyline;
use DXFighter\lib\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response; 


class SketchController extends Controller
{
    public function generateDXF(Request $request)
    {
        $requestData = $request->validate([
            'openings' => 'required|array',
            'order_id' => 'required|integer',
            'saveData' => 'boolean',
        ]);
        
        $requestOpenings = [];
        foreach ($requestData['openings'] as $opening) {
            $requestOpenings[$opening['id']] = $opening;
        }
        
        $order_id = $requestData['order_id'];
        
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($order_id);
        $this->getOrderParameters($order);

        foreach ($order->orderOpenings as $opening) {
            $opening->mp = $requestOpenings[$opening->id]['mp'];
            $opening->g = $requestOpenings[$opening->id]['g'];
            $opening->d = $requestOpenings[$opening->id]['d'];
            $opening->i = $requestOpenings[$opening->id]['i'];
            $opening->a = $requestOpenings[$opening->id]['a'];
            $opening->b = $requestOpenings[$opening->id]['b'];
            $opening->e = $requestOpenings[$opening->id]['e'];
            $opening->f = $requestOpenings[$opening->id]['f'];
        }

        $order->orderOpenings = $order->orderOpenings->reverse();

        $currentY = 0; 
        $holes = $this->getHolesCoordinates($order->orderOpenings);

        foreach ($order->orderOpenings as $index => $opening) {
            $opening['coordinates'] = $this->getOpeningSketchCoordinates($opening['doorsWidths'], $opening['doorsHeight'], $currentY);
            $currentY += $opening['doorsHeight'] + 250; 
            $opening['doorHandleHolesCoordinates'] = $holes[$index];
        }
        
        $glassItemIDs = Item::where('category_id', 1)->pluck('id');
        $glassOrderItem = $order->orderItems->whereIn('item_id', $glassItemIDs)->first();
        $glass = Item::where('id', $glassOrderItem->item_id)->first();

        return $this->DXFighterGenerator($order->orderOpenings, $glass, $order->order_number);
    }

    /**
     * Calculates the total glass area based on calculated door widths and heights
     *
     * @param int $orderId
     * @return array Returns array with total_area_m2, total_doors, and detailed breakdown by opening
     */
    public static function calculateGlassArea($orderId)
    {
        $order = Order::with(['orderOpenings'])->findOrFail($orderId);
        static::getOrderParametersStatic($order);
        
        $totalArea = 0; // in square millimeters
        $totalDoors = 0;
        $openingsBreakdown = [];
        
        foreach ($order->orderOpenings as $opening) {
            $openingArea = 0;
            $doorCount = count($opening['doorsWidths']);
            $totalDoors += $doorCount;
            
            foreach ($opening['doorsWidths'] as $doorWidth) {
                $doorArea = $doorWidth * $opening['doorsHeight'];
                $openingArea += $doorArea;
                $totalArea += $doorArea;
            }
            
            $openingsBreakdown[] = [
                'opening_id' => $opening->id,
                'door_count' => $doorCount,
                'door_widths' => $opening['doorsWidths'],
                'door_height' => $opening['doorsHeight'],
                'opening_area_m2' => round($openingArea / 1000000, 2)
            ];
        }
        
        return [
            'total_area_m2' => round($totalArea / 1000000, 2),
            'total_doors' => $totalDoors,
            'openings_breakdown' => $openingsBreakdown
        ];
    }

    /**
     * Static version of getOrderParameters for use in static context
     */
    private static function getOrderParametersStatic(Order $order)
    {
        $openings = $order->orderOpenings;
        $orderDoorHandles = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->item->category_id == 29;
        })->pluck('item');

        foreach ($openings as $opening) {
            $opening['doorsWidths'] = static::getOpeningDoorsWidthsStatic($opening);
            $opening['doorsHeight'] = $opening['height'] - 103;
        }
    }

    /**
     * Static version of getOpeningDoorsWidths for use in static context
     */
    private static function getOpeningDoorsWidthsStatic(OrderOpening $opening)
    {
        $stvorki = $opening['doors'];
        
        // Prevent division by zero - return empty array if no doors
        if ($stvorki <= 0) {
            return [];
        }
        
        $gap = $opening['type'] == 'center' ? $opening['a'] + $opening['b'] + $opening['e'] + $opening['g'] + 3 : 130;
        $doorsGap = [
            'start' => $opening['e'] + $opening['g'],
            'end' => $opening['b'],
        ];

        $divisor = $stvorki / ($opening['type'] == 'center' ? 2 : 1);
        
        // Prevent division by zero
        if ($divisor <= 0) {
            return [];
        }
        
        $overlaps = $divisor - 1;
        $middle = intval(($overlaps * 13) / $divisor);

        if ($opening['type'] == 'center') {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki / 2 - 2)) / 2);
        } else {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki - 2)) / 2);
        }

        $y = $opening['width'] / ($opening['type'] == 'center' ? 2 : 1) - $gap;
        $z = $y / $divisor;

        $shirinaStvorok = [];

        if ($opening['type'] == 'center') {
            for ($i = 1; $i <= $stvorki / 2; $i++) {
                $temp = $z + ($i == $stvorki / 2 || $i == 1 ? $edges : $middle);

                if ($i == $stvorki / 2) { $temp += $doorsGap['start']; } 
                else if ($i == 1) { $temp += $doorsGap['end']; }

                $shirinaStvorok[$i] = intval($temp);
                $shirinaStvorok[$stvorki - $i + 1] = intval($temp);
            }
        } else if ($opening['type'] == 'left' || $opening['type'] == 'right') {
            for ($i = 1; $i <= $stvorki; $i++) {
                $temp = $z + ($i == $stvorki || $i == 1 ? $edges : $middle);

                if ($i == $stvorki) { $temp += $doorsGap['start']; }
                else if ($i == 1) { $temp += $doorsGap['end']; }
                
                $shirinaStvorok[$i] = intval($temp);
            }
            
            if ($opening['type'] == 'left') {
                $shirinaStvorok = array_reverse($shirinaStvorok);
            }
        }

        ksort($shirinaStvorok);
        return $shirinaStvorok;
    }

    private function getOrderParameters(Order $order)
    {
        $openings = $order->orderOpenings;
        $orderDoorHandles = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->item->category_id == 29;
        })->pluck('item');

        foreach ($openings as $opening) {
            $opening['doorsWidths'] = $this->getOpeningDoorsWidths($opening);
            $opening['doorsHeight'] = $opening['height'] - 103;
        }
    }

    private function getOpeningDoorsWidths(OrderOpening $opening)
    {
        $stvorki = $opening['doors'];
        
        // Prevent division by zero - return empty array if no doors
        if ($stvorki <= 0) {
            return [];
        }
        
        $gap = $opening['type'] == 'center' ? $opening['a'] + $opening['b'] + $opening['e'] + $opening['g'] + 3 : 130;
        $doorsGap = [
            'start' => $opening['e'] + $opening['g'],
            'end' => $opening['b'],
        ];

        $divisor = $stvorki / ($opening['type'] == 'center' ? 2 : 1);
        
        // Prevent division by zero
        if ($divisor <= 0) {
            return [];
        }
        
        $overlaps = $divisor - 1;
        $middle = intval(($overlaps * 13) / $divisor);

        if ($opening['type'] == 'center') {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki / 2 - 2)) / 2);
        } else {
            $edges = intval(($overlaps * 13 - $middle * ($stvorki - 2)) / 2);
        }

        $y = $opening['width'] / ($opening['type'] == 'center' ? 2 : 1) - $gap;
        $z = $y / $divisor;

        $shirinaStvorok = [];

        if ($opening['type'] == 'center') {
            for ($i = 1; $i <= $stvorki / 2; $i++) {
                $temp = $z + ($i == $stvorki / 2 || $i == 1 ? $edges : $middle);

                if ($i == $stvorki / 2) { $temp += $doorsGap['start']; } 
                else if ($i == 1) { $temp += $doorsGap['end']; }

                $shirinaStvorok[$i] = intval($temp);
                $shirinaStvorok[$stvorki - $i + 1] = intval($temp);
            }
        } else if ($opening['type'] == 'left' || $opening['type'] == 'right') {
            for ($i = 1; $i <= $stvorki; $i++) {
                $temp = $z + ($i == $stvorki || $i == 1 ? $edges : $middle);

                if ($i == $stvorki) { $temp += $doorsGap['start']; }
                else if ($i == 1) { $temp += $doorsGap['end']; }
                
                $shirinaStvorok[$i] = intval($temp);
            }
            
            if ($opening['type'] == 'left') {
                $shirinaStvorok = array_reverse($shirinaStvorok);
            }
        }

        ksort($shirinaStvorok);
        return $shirinaStvorok;
    }

    private function getOpeningSketchCoordinates($openingDoorsWidths, $openingDoorsHeight, $startingY)
    {
        $coordinates = [];
        $starting_point = [0, $startingY];
        $h = $openingDoorsHeight;

        foreach ($openingDoorsWidths as $openingDoorWidth) {
            $w = $openingDoorWidth;

            $coordinates[] = [
                'P0' => [$starting_point[0], $starting_point[1], 0],
                'P1' => [$starting_point[0], $starting_point[1] + $h, 0],
                'P2' => [$starting_point[0] + $w, $starting_point[1] + $h, 0],
                'P3' => [$starting_point[0] + $w, $starting_point[1], 0],
            ];
            $starting_point[0] += $w + 100; 
        }
        return $coordinates;
    }

    /**
     * Calculates the coordinates and diameter for door handle holes for a list of openings.
     *
     * @param array $openings An array of openings, each expected to have keys like
     * 'door_handle_item_id', 'type', 'g', 'i', 'doorsHeight',
     * and potentially 'doors', 'doorsWidths' for 'right' type.
     * @return array An array containing hole data ('bottomHole', 'upperHole', 'diameter')
     * for each opening. Values can be null if holes are not calculated.
     */
    private function getHolesCoordinates($openings)
    {
        $openings = $openings->reverse();
        
        $calculatedCoordinates = [];
        $doorHandlePropertiesCache = [];

        $doorGap = 100; 

        foreach ($openings as $index => $opening) {
            $defaultHandleMP = $opening['mp'] ?? $opening['g'] - $opening['i'];
            $defaultHandleDiameter = $opening['d'] ?? 12;
            
            $openingType = $opening['type'] ?? null;
            $openingG = $opening['g'] ?? 0;
            $openingI = $opening['i'] ?? 0; 
            $doorHandleItemId = $opening['door_handle_item_id'] ?? null;
            $doorsHeight = $opening['doorsHeight'] ?? 0;

            $openingHoleSets = [];

            if (!$openingType || $doorsHeight <= 0) {
                $calculatedCoordinates[] = $openingHoleSets; 
                continue; 
            }

            $doorHandleMP = $defaultHandleMP;
            
            $doorHandleD = $defaultHandleDiameter;
            if ($doorHandleItemId) {
                if (!isset($doorHandlePropertiesCache[$doorHandleItemId])) {
                    $doorHandlePropertiesCache[$doorHandleItemId] = ItemProperty::where('item_id', $doorHandleItemId)->whereIn('name', ['МР', 'd'])->get();
                }
                $properties = $doorHandlePropertiesCache[$doorHandleItemId];
                $doorHandleMP = $properties?->firstWhere('name', 'МР')?->value ?? $defaultHandleMP;
                $doorHandleD = intval($properties?->firstWhere('name', 'd')?->value);
                $doorHandleD = $doorHandleD && $doorHandleD != 0  ? $doorHandleD : $defaultHandleDiameter;
            }

            $numberOfDoors = $opening['doors'] ?? 0;
            $doorWidths = (isset($opening['doorsWidths']) && is_array($opening['doorsWidths'])) ? $opening['doorsWidths'] : [];

            $cumulativeWidths = [0]; 
            $currentWidth = 0;
            for ($i = 1; $i <= $numberOfDoors; $i++) {
                $doorW = $doorWidths[$i] ?? 0;
                $currentWidth += $doorW;
                if ($i < $numberOfDoors) {
                    $currentWidth += $doorGap; 
                }
                $cumulativeWidths[$i] = $currentWidth;
            }
            
            if ($openingType == 'left') {
                $holeX = $openingG;
                $bottomY = $openingI;
                $upperY = $openingI + $doorHandleMP;

                $openingHoleSets[] = [
                    'bottomHole' => ['x' => $holeX, 'y' => $bottomY],
                    'upperHole' => ['x' => $holeX, 'y' => $upperY],
                    'diameter' => $doorHandleD
                ];
            } else if ($openingType == 'right') {
                if ($numberOfDoors > 0 && count($doorWidths) >= $numberOfDoors) {
                    $holeX = $cumulativeWidths[$numberOfDoors] - $openingG;

                    $bottomY = $openingI;
                    $upperY = $openingI + $doorHandleMP;

                    $openingHoleSets[] = [
                        'bottomHole' => ['x' => $holeX, 'y' => $bottomY],
                        'upperHole' => ['x' => $holeX, 'y' => $upperY],
                        'diameter' => $doorHandleD
                    ];
                }
            } else if ($openingType == 'center') {
                if ($numberOfDoors > 0 && $numberOfDoors % 2 == 0 && count($doorWidths) >= $numberOfDoors) {
                    $leftMiddleDoorIndex = $numberOfDoors / 2;
                    $rightMiddleDoorIndex = $numberOfDoors / 2 + 1;

                    $xLeftMiddle = $cumulativeWidths[$leftMiddleDoorIndex] - $openingG - $doorGap;
                    $xRightMiddle =  $cumulativeWidths[$leftMiddleDoorIndex] + $openingG;

                    $bottomY = $openingI;
                    $upperY = $openingI + $doorHandleMP;

                    $openingHoleSets[] = [
                        'bottomHole' => ['x' => $xLeftMiddle, 'y' => $bottomY],
                        'upperHole' => ['x' => $xLeftMiddle, 'y' => $upperY],
                        'diameter' => $doorHandleD
                    ];
                    $openingHoleSets[] = [
                        'bottomHole' => ['x' => $xRightMiddle, 'y' => $bottomY],
                        'upperHole' => ['x' => $xRightMiddle, 'y' => $upperY],
                        'diameter' => $doorHandleD
                    ];
                }
            }

            $calculatedCoordinates[] = $openingHoleSets;
        }
        
        return $calculatedCoordinates;
    }

    /**
     * Generates a DXF file containing polylines for openings and circles for door handle holes.
     *
     * @param array $openings An array of openings, each expected to have 'coordinates'
     * and 'doorHandleHolesCoordinates' keys.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     * Returns a downloadable DXF file response or a JSON error response.
     */
    private function DXFighterGenerator($openings, $glass, $orderNumber)
    {
        $dxf = new DXFighter();
        $entitiesAdded = 0;

        define('DXF_TEXT_HEIGHT', 50);
        define('DXF_TEXT_OFFSET', 25); 

        foreach ($openings as $index => $opening) {
            if (!isset($opening['coordinates']) || !is_array($opening['coordinates'])) {
                Log::warning("DXFFighterGenerator: Opening index $index skipped due to missing or invalid 'coordinates'.");
                continue;
            }

            $starting_point = isset($opening['coordinates'][0]['P0']) && is_array($opening['coordinates'][0]['P0'])
                                ? $opening['coordinates'][0]['P0']
                                : [0, 0, 0];

            foreach ($opening['coordinates'] as $coordSetIndex => $coordinateSet) {
                if (
                    !isset($coordinateSet['P0'], $coordinateSet['P1'], $coordinateSet['P2'], $coordinateSet['P3']) ||
                    !is_array($coordinateSet['P0']) || !is_array($coordinateSet['P1']) ||
                    !is_array($coordinateSet['P2']) || !is_array($coordinateSet['P3']) ||
                    count($coordinateSet['P0']) < 2 || count($coordinateSet['P1']) < 2 || 
                    count($coordinateSet['P3']) < 2                                      
                ) {
                    Log::warning("DXFFighterGenerator: Coordinate set index $coordSetIndex for opening $index skipped due to missing or invalid points (P0, P1, P2, P3).");
                    continue;
                }

                $p0 = array_pad($coordinateSet['P0'], 3, 0);
                $p1 = array_pad($coordinateSet['P1'], 3, 0);
                $p2 = array_pad($coordinateSet['P2'], 3, 0);
                $p3 = array_pad($coordinateSet['P3'], 3, 0);


                $polyline = new Polyline();
                $polyline->setFlag(1, 1); 
                $polyline->setColor(0); 

                $polyline->addPoint($p0);
                $polyline->addPoint($p1);
                $polyline->addPoint($p2);
                $polyline->addPoint($p3);
                $polyline->addPoint($p0); 

                $dxf->addEntity($polyline);
                $entitiesAdded++;

                $width = sqrt(pow($p3[0] - $p0[0], 2) + pow($p3[1] - $p0[1], 2));

                $height = sqrt(pow($p1[0] - $p0[0], 2) + pow($p1[1] - $p0[1], 2));

                $widthTextStr = sprintf("%d", (int)$width);
                $heightTextStr = sprintf("%d", (int)$height);

                $widthTextPosX = ($p0[0] + $p1[0]) / 2 + $width / 2;
                $widthTextPosY = min($p0[1], $p1[1]) - DXF_TEXT_OFFSET * 3; 
                $widthTextPosX -= (strlen($widthTextStr) * DXF_TEXT_HEIGHT) / 2;

                $heightTextPosX = min($p0[0], $p3[0]) - DXF_TEXT_OFFSET; 
                $heightTextPosY = ($p0[1] + $p3[1]) / 2 + $height / 2;
                $heightTextPosY -= (strlen($heightTextStr) * (DXF_TEXT_HEIGHT)) / 2;

                $widthTextEntity = new Text(
                    $widthTextStr,
                    [$widthTextPosX, $widthTextPosY, $p0[2]],
                    DXF_TEXT_HEIGHT,
                    0 
                );
                $heightTextEntity = new Text(
                    $heightTextStr,
                    [$heightTextPosX, $heightTextPosY, $p0[2]],
                    DXF_TEXT_HEIGHT,
                    90
                );

                $dxf->addEntity($widthTextEntity);
                $entitiesAdded++;
                $dxf->addEntity($heightTextEntity);
                $entitiesAdded++;
            } 

            $openingHoleSets = $opening['doorHandleHolesCoordinates'] ?? [];

            if (!empty($openingHoleSets) && is_array($openingHoleSets)) {
                foreach ($openingHoleSets as $holeSetIndex => $holeSet) {
                    if (
                        isset($holeSet['bottomHole']['x'], $holeSet['bottomHole']['y']) &&
                        isset($holeSet['upperHole']['x'], $holeSet['upperHole']['y']) &&
                        isset($holeSet['diameter']) && is_numeric($holeSet['diameter'])
                    ) {
                        $radius = $holeSet['diameter'] / 2;

                        $bottomHoleCenter = [
                            $starting_point[0] + $holeSet['bottomHole']['x'],
                            $starting_point[1] + $holeSet['bottomHole']['y'],
                            $starting_point[2] 
                        ];
                        $upperHoleCenter = [
                            $starting_point[0] + $holeSet['upperHole']['x'],
                            $starting_point[1] + $holeSet['upperHole']['y'],
                            $starting_point[2] 
                        ];

                        $circle1 = new Circle($bottomHoleCenter, $radius);
                        $circle2 = new Circle($upperHoleCenter, $radius);
                         $circle1->setColor(0); 
                         $circle2->setColor(0); 

                        $dxf->addEntity($circle1);
                        $dxf->addEntity($circle2);
                        $entitiesAdded += 2;
                    } else {
                        Log::warning("DXFFighterGenerator: Hole set index $holeSetIndex for opening $index skipped due to invalid data.");
                    }
                } 
            }
        } 

        if ($entitiesAdded === 0) {
            Log::error("DXFFighterGenerator: No valid geometry found to generate DXF.");
            return Response::json(['error' => 'No valid geometry found to generate DXF.'], 400);
        }
        
        $starting_pointY = 0;
        $starting_pointX = 0;
        $totalDoors = 0;
        $totalArea = 0;
        $glassName = $glass->name ?? '';
        
        foreach ($openings as $opening) {
            $starting_pointY += $opening['doorsHeight'] + 250; 
            $totalDoors += $opening['doors'];
            foreach ($opening['doorsWidths'] as $doorWidth) {
                $totalArea += $doorWidth * $opening['doorsHeight'];
            }
        }
        $totalArea = number_format($totalArea / 1000000, 2, '.', ' ');
        
        $text = 'Polirovka zakalka|' . $totalDoors . 'sht. (kol-vo stekol) S ' . $totalArea . ' m2 (ploshad stekol)|' . $this->cyrillicToLatin($glassName) . '|' . $this->cyrillicToLatin($orderNumber);
        
        $textNodes = explode('|', $text);
        foreach ($textNodes as $textNode) {
            $dxf->addEntity(new Text($textNode, [$starting_pointX, $starting_pointY, 0], DXF_TEXT_HEIGHT));
            $starting_pointY += DXF_TEXT_HEIGHT + 25;
        }

        $storageDir = storage_path('app/dxf_exports');
        $fileName = 'output_' . uniqid() . '.dxf';
        $filePath = $storageDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            if (!is_dir($storageDir)) {
                if (!mkdir($storageDir, 0775, true)) {
                    Log::error("DXFFighterGenerator: Failed to create storage directory: $storageDir");
                    return Response::json(['error' => 'Failed to create storage directory.'], 500);
                }
            }

            $dxf->saveAs($filePath);

             if (!file_exists($filePath)) {
                Log::error("DXFFighterGenerator: DXF file was not created at path: $filePath after saveAs() call.");
                return Response::json(['error' => 'Failed to save DXF file.'], 500);
            }

             return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error("DXFFighterGenerator: Failed to save or send DXF file. Path: $filePath. Error: " . $e->getMessage(), ['exception' => $e]);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return Response::json(['error' => 'Failed to generate or send DXF file.'], 500);
        }
    }
    
    /**
     * Generate and download a sketch DXF for an order using its stored data.
     */
    public function downloadSketchDXF(int $order_id)
    {
        $user = auth()->user();
        
        // Check if user owns this order and has sketcher access
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($order_id);
        
        if ((!$user->can('access app sketcher') || $order->user_id !== $user->id) && !$user->hasRole('Super-Admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check DXF access permission
        if (!$user->can('access app dxf')) {
            return response()->json(['error' => 'DXF access denied'], 403);
        }

        // Convert stored openings to the format expected by the DXF generator
        $openings = $order->orderOpenings->map(function ($opening) {
            return [
                'id' => $opening->id,
                'type' => $opening->type,
                'doors' => $opening->doors,
                'width' => $opening->width,
                'height' => $opening->height,
                'a' => $opening->a,
                'b' => $opening->b,
                'd' => $opening->d,
                'e' => $opening->e,
                'f' => $opening->f,
                'g' => $opening->g,
                'i' => $opening->i,
                'mp' => $opening->mp ?? 0,
                'door_handle_item_id' => $opening->door_handle_item_id,
            ];
        })->toArray();

        // Create a mock request object for the existing generateDXF method
        $request = new Request();
        $request->merge([
            'openings' => $openings,
            'order_id' => $order_id,
            'saveData' => false,
        ]);

        // Use the existing generateDXF method
        return $this->generateDXF($request);
    }

    private function cyrillicToLatin($text) {
        $cyrillicToLatinMap = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
            'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
            'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH',
            'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
            'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA'
        );
    
        return strtr($text, $cyrillicToLatinMap);
    }
}
