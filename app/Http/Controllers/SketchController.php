<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\Order;
use App\Models\OrderOpening;
use DXFighter\DXFighter;
use DXFighter\lib\Circle;
use DXFighter\lib\Polyline;
use Illuminate\Support\Facades\Log;

class SketchController extends Controller
{
    public function getOrderParameters(Order $order)
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
            $starting_point[0] += $w + 100; // 100 is a gap between doors
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
        $calculatedCoordinates = [];
        $doorHandlePropertiesCache = [];

        $doorGap = 100; 
        $defaultHandleDiameter = 12;
        $defaultHandleMP = 0; 

        foreach ($openings as $index => $opening) {
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
                $doorHandleD = $properties?->firstWhere('name', 'd')?->value ?? $defaultHandleDiameter;
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
    private function DXFighterGenerator($openings)
    {
        $dxf = new DXFighter();
        $entitiesAdded = 0;

        foreach ($openings as $index => $opening) {
            if (!isset($opening['coordinates']) || !is_array($opening['coordinates'])) {
                continue;
            }

            $starting_point = isset($opening['coordinates'][0]['P0']) ? $opening['coordinates'][0]['P0'] : [0, 0, 0];

            foreach ($opening['coordinates'] as $coordSetIndex => $coordinateSet) {
                if (!isset($coordinateSet['P0'], $coordinateSet['P1'], $coordinateSet['P2'], $coordinateSet['P3'])) {
                    continue;
                }

                $polyline = new Polyline();
                $polyline->setFlag(0, 1);
                $polyline->setColor(0);

                $polyline->addPoint($coordinateSet['P0']);
                $polyline->addPoint($coordinateSet['P1']);
                $polyline->addPoint($coordinateSet['P2']);
                $polyline->addPoint($coordinateSet['P3']);
                $polyline->addPoint($coordinateSet['P0']);

                $dxf->addEntity($polyline);
                $entitiesAdded++;
            }

            $openingHoleSets = $opening['doorHandleHolesCoordinates'] ?? [];

            if (!empty($openingHoleSets)) {
                foreach ($openingHoleSets as $holeSetIndex => $holeSet) {
                    $hasValidHoleData = isset($holeSet['bottomHole']['x'], $holeSet['bottomHole']['y']) &&
                        isset($holeSet['upperHole']['x'], $holeSet['upperHole']['y']) &&
                        isset($holeSet['diameter']);

                    if ($hasValidHoleData) {
                        $radius = $holeSet['diameter'] / 2;

                        $bottomHoleCenter = [
                            $starting_point[0] + $holeSet['bottomHole']['x'],
                            $starting_point[1] + $holeSet['bottomHole']['y'],
                            0 
                        ];
                        $upperHoleCenter = [
                            $starting_point[0] + $holeSet['upperHole']['x'],
                            $starting_point[1] + $holeSet['upperHole']['y'],
                            0
                        ];

                        $circle1 = new Circle($bottomHoleCenter, $radius);
                        $circle2 = new Circle($upperHoleCenter, $radius);

                        $dxf->addEntity($circle1);
                        $dxf->addEntity($circle2);
                        $entitiesAdded += 2;
                    } 
                } 
            } 
        } 

        if ($entitiesAdded === 0) {
            return response()->json(['error' => 'No valid geometry found to generate DXF.'], 400); // Bad Request
        }

        $storageDir = storage_path('app');
        $fileName = 'output_' . uniqid() . '.dxf'; 
        $filePath = $storageDir . DIRECTORY_SEPARATOR . $fileName; 

        try {
            if (!is_dir($storageDir)) {
                if (!mkdir($storageDir, 0775, true)) {
                    Log::error("DXFFighterGenerator: Failed to create storage directory: $storageDir");
                    return response()->json(['error' => 'Failed to create storage directory.'], 500);
                }
            }

            $dxf->saveAs($filePath);
            
            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error("DXFFighterGenerator: Failed to save or send DXF file. Error: " . $e->getMessage());
            
            if (file_exists($filePath)) { unlink($filePath); }
            
            return response()->json(['error' => 'Failed to generate or send DXF file.'], 500); // Internal Server Error
        }
    }

    public function generateDXF($order_id = 36)
    {
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($order_id);
        $this->getOrderParameters($order);

        $currentY = 0; 
        $holes = $this->getHolesCoordinates($order->orderOpenings);

        foreach ($order->orderOpenings as $index => $opening) {
            $opening['coordinates'] = $this->getOpeningSketchCoordinates($opening['doorsWidths'], $opening['doorsHeight'], $currentY);
            $currentY += $opening['doorsHeight'] + 250; // Add 250mm gap between openings (vertical)
            $opening['doorHandleHolesCoordinates'] = $holes[$index];
        }

        return $this->DXFighterGenerator($order->orderOpenings);
    }
}
