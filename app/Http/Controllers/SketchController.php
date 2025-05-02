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
    // Function to convert order parameters to XY coordinates
    public function getOrderParameters(Order $order) {
        $openings = $order->orderOpenings;
        $orderDoorHandles = $order->orderItems->filter(function ($orderItem) {
            return $orderItem->item->category_id == 29;
        })->pluck('item');

        foreach ($openings as $opening) {
            $opening['doorsWidths'] = $this->getOpeningDoorsWidths($opening);
            $opening['doorsHeight'] = $opening['height'] - 103;
        }
    }

    private function getOpeningDoorsWidths(OrderOpening $opening) {
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

                if ($i == $stvorki / 2) {
                    $temp += $doorsGap['start'];
                } else if ($i == 1) {
                    $temp += $doorsGap['end'];
                }

                $shirinaStvorok[$i] = intval($temp);
                $shirinaStvorok[$stvorki - $i + 1] = intval($temp);
            }
        } else if ($opening['type'] == 'left' || $opening['type'] == 'right') {
            for ($i = 1; $i <= $stvorki; $i++) {
                $temp = $z + ($i == $stvorki || $i == 1 ? $edges : $middle);

                if ($i == $stvorki) {
                    $temp += $doorsGap['start'];
                } else if ($i == 1) {
                    $temp += $doorsGap['end'];
                }

                $shirinaStvorok[$i] = intval($temp);
            }
        }
        
        ksort($shirinaStvorok);
        
        Log::info($opening['type']);
        Log::info($shirinaStvorok);

        return $shirinaStvorok;
    }

    private function getOpeningSketchCoordinates($openingDoorsWidths, $openingDoorsHeight, $startingY) {
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
        $doorHandlePropertiesCache = []; // Cache DB results per item_id
    
        // Constants/Defaults
        $doorGap = 100; // Assumed gap used in width calculation
        // $verticalGapBetweenOpenings = 250; // This seems unused in hole coordinate calculation itself
        $defaultHandleDiameter = 12;
        $defaultHandleMP = 0; // Default center-to-center distance
    
        foreach ($openings as $index => $opening) {
            // --- 1. Extract & Validate Essential Opening Data ---
            $openingType = $opening['type'] ?? null;
            $openingG = $opening['g'] ?? 0; // Horizontal offset from reference edge
            $openingI = $opening['i'] ?? 0; // Vertical offset from bottom edge
            $doorHandleItemId = $opening['door_handle_item_id'] ?? null;
            $doorsHeight = $opening['doorsHeight'] ?? 0;
    
            // Array to hold hole sets for the current opening
            $openingHoleSets = [];
    
            if (!$openingType || $doorsHeight <= 0) {
                // Log::warning("Skipping opening index $index: Missing type or invalid doorsHeight.");
                 $calculatedCoordinates[] = $openingHoleSets; // Add empty array for this opening
                 continue; // Skip hole calculation for this opening
            }
    
            // --- 2. Get Handle Properties (MP and Diameter) using Cache ---
            $doorHandleMP = $defaultHandleMP;
            $doorHandleD = $defaultHandleDiameter;
            if ($doorHandleItemId) {
                // Fetch/Retrieve from cache
                if (!isset($doorHandlePropertiesCache[$doorHandleItemId])) {
                    // Assuming ItemProperty is a valid Eloquent model or similar DB access
                    $doorHandlePropertiesCache[$doorHandleItemId] = ItemProperty::where('item_id', $doorHandleItemId)->whereIn('name', ['МР', 'd'])->get();
                }
                $properties = $doorHandlePropertiesCache[$doorHandleItemId];
                // Extract values concisely using optional chaining and null coalescing
                $doorHandleMP = $properties?->firstWhere('name', 'МР')?->value ?? $defaultHandleMP;
                $doorHandleD = $properties?->firstWhere('name', 'd')?->value ?? $defaultHandleDiameter;
            }
    
            // --- 3. Calculate Hole Coordinates based on Opening Type ---
            $numberOfDoors = $opening['doors'] ?? 0;
            $doorWidths = (isset($opening['doorsWidths']) && is_array($opening['doorsWidths'])) ? $opening['doorsWidths'] : [];
    
            // Calculate cumulative widths for door positioning
            $cumulativeWidths = [0]; // Start with 0 before the first door
            $currentWidth = 0;
            for ($i = 1; $i <= $numberOfDoors; $i++) {
                $doorW = $doorWidths[$i] ?? 0;
                $currentWidth += $doorW;
                if ($i < $numberOfDoors) {
                    $currentWidth += $doorGap; // Add gap after door, except the last one
                }
                $cumulativeWidths[$i] = $currentWidth;
            }
            // Log::info("Opening $index ($openingType): Cumulative Widths: " . json_encode($cumulativeWidths));
    
    
            if ($openingType == 'left') {
                // Holes are on the right side of the single door (or the first door if multi-door left)
                // X coordinate is 'g' from the left edge of the first door
            $holeX = $openingG;
            $bottomY = $openingI;
            $upperY = $openingI + $doorHandleMP;

            $openingHoleSets[] = [
                'bottomHole' => ['x' => $holeX, 'y' => $bottomY],
                'upperHole' => ['x' => $holeX, 'y' => $upperY],
                'diameter' => $doorHandleD
            ];

        } else if ($openingType == 'right') {
            // Holes are on the left side of the single door (or the last door if multi-door right)
            if ($numberOfDoors > 0 && count($doorWidths) >= $numberOfDoors) {
                 // X coordinate is 'g' from the right edge of the last door
                 // Total width up to the end of the last door is $cumulativeWidths[$numberOfDoors]
                 $holeX = $cumulativeWidths[$numberOfDoors] - $openingG;

                 $bottomY = $openingI;
                 $upperY = $openingI + $doorHandleMP;

                 $openingHoleSets[] = [
                     'bottomHole' => ['x' => $holeX, 'y' => $bottomY],
                     'upperHole' => ['x' => $holeX, 'y' => $upperY],
                     'diameter' => $doorHandleD
                 ];

            } else {
                 // Log::warning("Opening index $index (right): Missing valid 'doors' or 'doorsWidths'. No holes calculated.");
            }
    
            } else if ($openingType == 'center') {
                // Holes are on the right side of the left-middle door
                // AND on the left side of the right-middle door.
                // Assumes an even number of doors.
                if ($numberOfDoors > 0 && $numberOfDoors % 2 == 0 && count($doorWidths) >= $numberOfDoors) {
                    $leftMiddleDoorIndex = $numberOfDoors / 2;
                    $rightMiddleDoorIndex = $numberOfDoors / 2 + 1;
    
                    // --- Holes for the left-middle door (right side) ---
                    // X is 'g' from the left edge of the left-middle door
                    $xLeftMiddle = $cumulativeWidths[$leftMiddleDoorIndex] - $openingG - $doorGap;
    
                    $bottomY = $openingI;
                    $upperY = $openingI + $doorHandleMP;
    
                    $openingHoleSets[] = [
                        'bottomHole' => ['x' => $xLeftMiddle, 'y' => $bottomY],
                        'upperHole' => ['x' => $xLeftMiddle, 'y' => $upperY],
                        'diameter' => $doorHandleD
                    ];
    
                    // --- Holes for the right-middle door (left side) ---
                    // X is 'g' from the right edge of the right-middle door
                    // The right edge of the right-middle door is at $cumulativeWidths[$rightMiddleDoorIndex]
                    $xRightMiddle =  $cumulativeWidths[$leftMiddleDoorIndex] + $openingG;
    
                     $openingHoleSets[] = [
                         'bottomHole' => ['x' => $xRightMiddle, 'y' => $bottomY],
                         'upperHole' => ['x' => $xRightMiddle, 'y' => $upperY],
                        // 'bottomHole' => ['x' => 0, 'y' => 0],
                        // 'upperHole' => ['x' => 0, 'y' => 0],
                        'diameter' => $doorHandleD
                     ];
                     
                     Log::info('Hole coordinates: ' . $xLeftMiddle . ', ' . $xRightMiddle);
    
                     // Log::info("Opening index $index (center): Added 2 sets of holes.");
    
                } else {
                     // Log::warning("Opening index $index (center): Invalid 'doors' count (must be even > 0) or 'doorsWidths'. No holes calculated.");
                }
            }
            // else if ($openingType == 'fixed') {
            //     // No holes for fixed panels
            // }
    
    
            // --- 4. Store Calculated Hole Sets for the Current Opening ---
            $calculatedCoordinates[] = $openingHoleSets;
    
            // --- 5. Accumulate Height Offset (This seems for positioning subsequent openings, not holes within current) ---
            // $cumulativeHeightOffset += $doorsHeight + $verticalGapBetweenOpenings;
    
        } // End foreach
    
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
    private function DXFighterGenerator($openings) {
        $dxf = new DXFighter();
        $entitiesAdded = 0; // Counter for debugging/validation
    
        foreach ($openings as $index => $opening) {
            // --- 1. Add Polylines for the opening boundaries ---
            if (!isset($opening['coordinates']) || !is_array($opening['coordinates'])) {
                // Log::warning("DXFFighterGenerator: Opening at index $index is missing 'coordinates' array. Skipping polyline generation for this opening.");
                continue; // Skip to the next opening if coordinates are missing
            }
    
            // Assuming the first coordinate set's P0 is the origin for this opening's entities
            $starting_point = isset($opening['coordinates'][0]['P0']) ? $opening['coordinates'][0]['P0'] : [0, 0, 0];
    
            foreach ($opening['coordinates'] as $coordSetIndex => $coordinateSet) {
                // Ensure the required points exist in the coordinate set
                if (!isset($coordinateSet['P0'], $coordinateSet['P1'], $coordinateSet['P2'], $coordinateSet['P3'])) {
                    // Log::warning("DXFFighterGenerator: Coordinate set $coordSetIndex in opening $index is missing points (P0-P3). Skipping this polyline.");
                    continue; // Skip this specific polyline
                }
    
                $polyline = new Polyline();
                $polyline->setFlag(0, 1); // Flag 1 typically means a closed polyline
                $polyline->setColor(140); // Set color index (e.g., 140 is often cyan/blue)
    
                // Add vertices to form a closed shape (assuming rectangular)
                // Note: These points are assumed to be absolute coordinates already.
                // If they were relative to the opening's origin, we'd add $starting_point here.
                // Based on the previous code, they seem absolute, but the hole logic adds $starting_point.
                // Let's assume coordinates are absolute for the polyline for now, and relative for holes.
                $polyline->addPoint($coordinateSet['P0']);
                $polyline->addPoint($coordinateSet['P1']);
                $polyline->addPoint($coordinateSet['P2']);
                $polyline->addPoint($coordinateSet['P3']);
                $polyline->addPoint($coordinateSet['P0']); // Close the polyline by adding the start point again
    
                $dxf->addEntity($polyline);
                $entitiesAdded++;
            }
    
            // --- 2. Add Door Handle Holes (if applicable and valid) ---
            // $opening['doorHandleHolesCoordinates'] now contains an array of hole sets
            $openingHoleSets = $opening['doorHandleHolesCoordinates'] ?? [];
    
            if (!empty($openingHoleSets)) {
                // Iterate through each set of holes calculated for this opening
                foreach ($openingHoleSets as $holeSetIndex => $holeSet) {
                    // Validate that hole data structure is complete before proceeding for this set
                    $hasValidHoleData = isset($holeSet['bottomHole']['x'], $holeSet['bottomHole']['y']) &&
                                        isset($holeSet['upperHole']['x'], $holeSet['upperHole']['y']) &&
                                        isset($holeSet['diameter']);
    
                    if ($hasValidHoleData) {
                        $radius = $holeSet['diameter'] / 2;
    
                        // Calculate absolute center points for the circles by adding the opening's starting point
                        $bottomHoleCenter = [
                            $starting_point[0] + $holeSet['bottomHole']['x'],
                            $starting_point[1] + $holeSet['bottomHole']['y'],
                            0 // Assuming Z=0 for 2D DXF
                        ];
                        $upperHoleCenter = [
                            $starting_point[0] + $holeSet['upperHole']['x'],
                            $starting_point[1] + $holeSet['upperHole']['y'],
                            0 // Assuming Z=0
                        ];
    
                        // Log::info("DXFFighterGenerator: Opening $index, Hole Set $holeSetIndex - Starting point: " . json_encode($starting_point) . ", Bottom hole center: " . json_encode($bottomHoleCenter) . ", Upper hole center: " . json_encode($upperHoleCenter));
    
                        // Create Circle entities
                        $circle1 = new Circle($bottomHoleCenter, $radius);
                        $circle2 = new Circle($upperHoleCenter, $radius);
                        // Optional: Set color or other properties for circles if needed
                        // $circle1->setColor(COLOR_RED); // Example: Assuming COLOR_RED constant exists
                        // $circle2->setColor(COLOR_RED);
    
                        $dxf->addEntity($circle1);
                        $dxf->addEntity($circle2);
                        $entitiesAdded += 2;
    
                        // Log::info("DXFFighterGenerator: Added 2 door handle holes for opening index $index (Set $holeSetIndex).");
    
                    } else {
                        // Log::warning("DXFFighterGenerator: Skipping invalid hole set $holeSetIndex for opening index $index.");
                    }
                } // End foreach $openingHoleSets
            } // End if !empty($openingHoleSets)
        } // End foreach $openings
    
        if ($entitiesAdded === 0) {
            // Log::warning("DXFFighterGenerator: No entities were added to the DXF file. Check input data.");
            return response()->json(['error' => 'No valid geometry found to generate DXF.'], 400); // Bad Request
        }
    
        // --- 3. Save and Return the DXF File ---
        $storageDir = storage_path('app');
        $fileName = 'output_' . uniqid() . '.dxf'; // Use unique filename to avoid collisions
        $filePath = $storageDir . DIRECTORY_SEPARATOR . $fileName; // Use directory separator constant
    
        try {
            // Ensure storage directory exists
            if (!is_dir($storageDir)) {
                if (!mkdir($storageDir, 0775, true)) {
                     Log::error("DXFFighterGenerator: Failed to create storage directory: $storageDir");
                     return response()->json(['error' => 'Failed to create storage directory.'], 500);
                }
            }
    
            // Save the DXF file
            $dxf->saveAs($filePath);
            // Log::info("DXFFighterGenerator: Successfully saved DXF file to $filePath");
    
            // Return the file as a download and delete it after sending
            // Using Laravel's response helper
            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    
        } catch (\Exception $e) {
            Log::error("DXFFighterGenerator: Failed to save or send DXF file. Error: " . $e->getMessage());
            // Clean up the file if it exists and an error occurred before sending
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return response()->json(['error' => 'Failed to generate or send DXF file.'], 500); // Internal Server Error
        }
    }

    public function generateDXF($order_id = 36) {
        $order = Order::with(['orderOpenings', 'orderItems.item'])->findOrFail($order_id);
        $this->getOrderParameters($order);

        $currentY = 0; // Initialize the starting Y coordinate
        $holes = $this->getHolesCoordinates($order->orderOpenings);

        // Log coordinates just to see if it works
        foreach ($order->orderOpenings as $index => $opening) {
            $opening['coordinates'] = $this->getOpeningSketchCoordinates($opening['doorsWidths'], $opening['doorsHeight'], $currentY);
            $currentY += $opening['doorsHeight'] + 250; // Update the starting Y coordinate for the next opening
            // Assign door handle holes coordinates to each opening
            $opening['doorHandleHolesCoordinates'] = $holes[$index];
        }

        return $this->DXFighterGenerator($order->orderOpenings);
    }
}
