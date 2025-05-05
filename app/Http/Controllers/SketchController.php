<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\Order;
use App\Models\OrderOpening;
use DXFighter\DXFighter;
use DXFighter\lib\Circle;
use DXFighter\lib\Polyline;
use DXFighter\lib\Text;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response; // Assuming Laravel context


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

        // --- Configuration for Text ---
        // Adjust these values as needed for your drawing scale
        define('DXF_TEXT_HEIGHT', 50); // Height of the dimension text characters
        define('DXF_TEXT_OFFSET', 25); // Distance to offset text from the shape edge
        // --- End Configuration ---

        foreach ($openings as $index => $opening) {
            if (!isset($opening['coordinates']) || !is_array($opening['coordinates'])) {
                Log::warning("DXFFighterGenerator: Opening index $index skipped due to missing or invalid 'coordinates'.");
                continue;
            }

            // Use the first P0 as the reference starting point for hole offsets
            $starting_point = isset($opening['coordinates'][0]['P0']) && is_array($opening['coordinates'][0]['P0'])
                                ? $opening['coordinates'][0]['P0']
                                : [0, 0, 0];

            foreach ($opening['coordinates'] as $coordSetIndex => $coordinateSet) {
                // Validate that all required points exist and are arrays
                if (
                    !isset($coordinateSet['P0'], $coordinateSet['P1'], $coordinateSet['P2'], $coordinateSet['P3']) ||
                    !is_array($coordinateSet['P0']) || !is_array($coordinateSet['P1']) ||
                    !is_array($coordinateSet['P2']) || !is_array($coordinateSet['P3']) ||
                    count($coordinateSet['P0']) < 2 || count($coordinateSet['P1']) < 2 || // Need at least x, y
                    count($coordinateSet['P3']) < 2                                       // Need P0, P1, P3 for dimensions
                ) {
                    Log::warning("DXFFighterGenerator: Coordinate set index $coordSetIndex for opening $index skipped due to missing or invalid points (P0, P1, P2, P3).");
                    continue;
                }

                // Ensure Z-coordinate exists, default to 0 if not
                $p0 = array_pad($coordinateSet['P0'], 3, 0);
                $p1 = array_pad($coordinateSet['P1'], 3, 0);
                $p2 = array_pad($coordinateSet['P2'], 3, 0);
                $p3 = array_pad($coordinateSet['P3'], 3, 0);


                $polyline = new Polyline();
                $polyline->setFlag(1, 1); // Set flag 1 (Closed polyline) - bit 0 = 1
                $polyline->setColor(0); // Default color BYLAYER

                $polyline->addPoint($p0);
                $polyline->addPoint($p1);
                $polyline->addPoint($p2);
                $polyline->addPoint($p3);
                $polyline->addPoint($p0); // Not needed if flag 1 is set correctly

                $dxf->addEntity($polyline);
                $entitiesAdded++;

                // --- Add horizontal width and vertical height text ---

                // Calculate width (distance between P0 and P1 in XY plane)
                // Assuming P0-P1 is the width dimension
                $width = sqrt(pow($p3[0] - $p0[0], 2) + pow($p3[1] - $p0[1], 2));

                // Calculate height (distance between P0 and P3 in XY plane)
                // Assuming P0-P3 is the height dimension
                $height = sqrt(pow($p1[0] - $p0[0], 2) + pow($p1[1] - $p0[1], 2));

                // Format text labels
                $widthTextStr = sprintf("%d", (int)$width);
                $heightTextStr = sprintf("%d", (int)$height);

                // Determine text positions (slightly offset from the shape)
                // Width text: Centered below the P0-P1 edge
                $widthTextPosX = ($p0[0] + $p1[0]) / 2 + $width / 2;
                $widthTextPosY = min($p0[1], $p1[1]) - DXF_TEXT_OFFSET * 3; // Place below the lowest Y of P0/P1
                //subtract half of the text width from $widthTextPosY
                $widthTextPosX -= (strlen($widthTextStr) * DXF_TEXT_HEIGHT) / 2;

                // Height text: Centered left of the P0-P3 edge
                $heightTextPosX = min($p0[0], $p3[0]) - DXF_TEXT_OFFSET; // Place left of the leftmost X of P0/P3
                $heightTextPosY = ($p0[1] + $p3[1]) / 2 + $height / 2;
                //subtract half of the text width from $heightTextPosY
                $heightTextPosY -= (strlen($heightTextStr) * (DXF_TEXT_HEIGHT)) / 2;

                // Create Text entities
                // Text constructor: new Text(text, [x, y, z], height, rotation)
                // Rotation is in degrees, 0 for horizontal, 90 for vertical aligned with Y-axis
                $widthTextEntity = new Text(
                    $widthTextStr,
                    [$widthTextPosX, $widthTextPosY, $p0[2]], // Use Z from P0
                    DXF_TEXT_HEIGHT,
                    0 // Horizontal text
                );
                //  $widthTextEntity->setColor(1); // Set color to red (example)

                $heightTextEntity = new Text(
                    $heightTextStr,
                    [$heightTextPosX, $heightTextPosY, $p0[2]], // Use Z from P0
                    DXF_TEXT_HEIGHT,
                    90 // Vertical text (rotated 90 degrees counter-clockwise)
                );
                //  $heightTextEntity->setColor(3); // Set color to green (example)


                // Add text entities to DXF
                $dxf->addEntity($widthTextEntity);
                $entitiesAdded++;
                $dxf->addEntity($heightTextEntity);
                $entitiesAdded++;
                // --- End Text Addition ---
            } // End foreach coordinateSet

            // --- Handle Door Handle Holes ---
            $openingHoleSets = $opening['doorHandleHolesCoordinates'] ?? [];

            if (!empty($openingHoleSets) && is_array($openingHoleSets)) {
                foreach ($openingHoleSets as $holeSetIndex => $holeSet) {
                    if (
                        isset($holeSet['bottomHole']['x'], $holeSet['bottomHole']['y']) &&
                        isset($holeSet['upperHole']['x'], $holeSet['upperHole']['y']) &&
                        isset($holeSet['diameter']) && is_numeric($holeSet['diameter'])
                    ) {
                        $radius = $holeSet['diameter'] / 2;

                        // Calculate absolute center coordinates based on the opening's starting point
                        $bottomHoleCenter = [
                            $starting_point[0] + $holeSet['bottomHole']['x'],
                            $starting_point[1] + $holeSet['bottomHole']['y'],
                            $starting_point[2] // Use the Z from the starting point
                        ];
                        $upperHoleCenter = [
                            $starting_point[0] + $holeSet['upperHole']['x'],
                            $starting_point[1] + $holeSet['upperHole']['y'],
                            $starting_point[2] // Use the Z from the starting point
                        ];

                        $circle1 = new Circle($bottomHoleCenter, $radius);
                        $circle2 = new Circle($upperHoleCenter, $radius);
                         $circle1->setColor(5); // Blue
                         $circle2->setColor(5); // Blue

                        $dxf->addEntity($circle1);
                        $dxf->addEntity($circle2);
                        $entitiesAdded += 2;
                    } else {
                        Log::warning("DXFFighterGenerator: Hole set index $holeSetIndex for opening $index skipped due to invalid data.");
                    }
                } // End foreach holeSet
            }
            // --- End Handle Door Handle Holes ---

        } // End foreach openings

        if ($entitiesAdded === 0) {
            Log::error("DXFFighterGenerator: No valid geometry found to generate DXF.");
            // Use Laravel's response helper if in a Laravel project
            return Response::json(['error' => 'No valid geometry found to generate DXF.'], 400); // Bad Request
            // Or use standard PHP headers and json_encode if not in Laravel
            // header('Content-Type: application/json', true, 400);
            // echo json_encode(['error' => 'No valid geometry found to generate DXF.']);
            // exit;
        }

        // Define storage path relative to the Laravel project root
        $storageDir = storage_path('app/dxf_exports'); // Store in a subfolder for organization
        $fileName = 'output_' . uniqid() . '.dxf';
        $filePath = $storageDir . DIRECTORY_SEPARATOR . $fileName;

        try {
            // Ensure the directory exists
            if (!is_dir($storageDir)) {
                // Attempt to create directory recursively with appropriate permissions
                if (!mkdir($storageDir, 0775, true)) {
                    Log::error("DXFFighterGenerator: Failed to create storage directory: $storageDir");
                    return Response::json(['error' => 'Failed to create storage directory.'], 500);
                }
            }

            // Save the DXF file
            $dxf->saveAs($filePath);

            // Check if file was actually created before attempting download
             if (!file_exists($filePath)) {
                Log::error("DXFFighterGenerator: DXF file was not created at path: $filePath after saveAs() call.");
                return Response::json(['error' => 'Failed to save DXF file.'], 500);
            }


            Log::info("DXFFighterGenerator: Successfully generated DXF file: $filePath");

            // Return the file as a download and delete it after sending
            // Use Laravel's response helper
             return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
             // Or use standard PHP headers if not in Laravel:
             /*
             header('Content-Description: File Transfer');
             header('Content-Type: application/dxf'); // Or application/octet-stream
             header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
             header('Expires: 0');
             header('Cache-Control: must-revalidate');
             header('Pragma: public');
             header('Content-Length: ' . filesize($filePath));
             readfile($filePath);
             unlink($filePath); // Delete the file after sending
             exit;
             */

        } catch (\Exception $e) {
            Log::error("DXFFighterGenerator: Failed to save or send DXF file. Path: $filePath. Error: " . $e->getMessage(), ['exception' => $e]);

            // Clean up the potentially partially created file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Use Laravel's response helper
            return Response::json(['error' => 'Failed to generate or send DXF file.'], 500); // Internal Server Error
             // Or standard PHP
             /*
             header('Content-Type: application/json', true, 500);
             echo json_encode(['error' => 'Failed to generate or send DXF file.']);
             exit;
             */
        }
    }
    
    private function createTexts($openings) {
        
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
        
        $this->createTexts($order->orderOpenings);

        return $this->DXFighterGenerator($order->orderOpenings);
    }
}
