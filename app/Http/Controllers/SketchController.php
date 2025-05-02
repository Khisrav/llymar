<?php

namespace App\Http\Controllers;

use App\Models\ItemProperty;
use App\Models\Order;
use App\Models\OrderOpening;
use DXFighter\DXFighter;
use DXFighter\lib\Circle;
use DXFighter\lib\Layer;
use DXFighter\lib\Polyline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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
            for ($i = 0; $i < $stvorki / 2; $i++) {
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
            for ($i = 0; $i < $stvorki; $i++) {
                $temp = $z + ($i == $stvorki || $i == 1 ? $edges : $middle);

                if ($i == $stvorki) {
                    $temp += $doorsGap['start'];
                } else if ($i == 1) {
                    $temp += $doorsGap['end'];
                }

                $shirinaStvorok[$i] = intval($temp);
            }
        }

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
        $cumulativeHeightOffset = 0;
        $doorHandlePropertiesCache = []; // Cache DB results per item_id
    
        // Constants/Defaults
        $doorGap = 100; // Assumed gap used in width calculation for 'right' type
        $verticalGapBetweenOpenings = 250; // Assumed vertical offset between openings
        $defaultHandleDiameter = 12;
        $defaultHandleMP = 0; // Default center-to-center distance
    
        foreach ($openings as $index => $opening) {
            // --- 1. Extract & Validate Essential Opening Data ---
            $openingType = $opening['type'] ?? null;
            $openingG = $opening['g'] ?? 0;
            $openingI = $opening['i'] ?? 0;
            $doorHandleItemId = $opening['door_handle_item_id'] ?? null;
            $doorsHeight = $opening['doorsHeight'] ?? 0;
    
            if (!$openingType || $doorsHeight <= 0) {
                 Log::warning("Skipping opening index $index: Missing type or invalid doorsHeight.");
                 $calculatedCoordinates[] = ['bottomHole' => null, 'upperHole' => null, 'diameter' => null];
                 continue; // Skip this opening entirely
            }
    
            // --- 2. Get Handle Properties (MP and Diameter) using Cache ---
            $doorHandleMP = $defaultHandleMP;
            $doorHandleD = $defaultHandleDiameter;
            if ($doorHandleItemId) {
                // Fetch/Retrieve from cache
                if (!isset($doorHandlePropertiesCache[$doorHandleItemId])) {
                    $doorHandlePropertiesCache[$doorHandleItemId] = ItemProperty::where('item_id', $doorHandleItemId)->whereIn('name', ['МР', 'd'])->get();
                }
                $properties = $doorHandlePropertiesCache[$doorHandleItemId];
                // Extract values concisely
                $doorHandleMP = $properties?->firstWhere('name', 'МР')?->value ?? $defaultHandleMP;
                $doorHandleD = $properties?->firstWhere('name', 'd')?->value ?? $defaultHandleDiameter;
            }
    
            // --- 3. Calculate Hole Coordinates ---
            $bottomHole = null;
            $upperHole = null;
            $holeX = null; // Calculated X coordinate
    
            if ($openingType == 'left') {
                $holeX = $openingG; // 'g' from left edge
                $bottomY = $openingI;
                $upperY = $openingI + $doorHandleMP;
    
            } else if ($openingType == 'right') {
                $numberOfDoors = isset($opening['doors']) ? (int) $opening['doors'] : 0;
                $doorWidths = (isset($opening['doorsWidths']) && is_array($opening['doorsWidths'])) ? $opening['doorsWidths'] : [];
    
                if ($numberOfDoors > 0 && count($doorWidths) >= $numberOfDoors) {
                    // Calculate total width (sum of door widths + gaps)
                    $widthAccumulator = 0;
                    for ($i = 0; $i < $numberOfDoors; $i++) {
                        $widthAccumulator += ($doorWidths[$i] ?? 0) + $doorGap;
                    }
                    // **Position 'g' from the calculated right edge**
                    $holeX = $widthAccumulator - $openingG;
                    Log::info("Width acc: $widthAccumulator, openingG: $openingG, holeX: $holeX, doorsWidth: " . json_encode($doorWidths));

    
                    // Y coordinates use cumulative height offset
                    $bottomY = $openingI;
                    $upperY = $openingI + $doorHandleMP;
                } else {
                     Log::warning("Opening index $index (right): Missing valid 'doors' or 'doorsWidths'. No holes calculated.");
                }
            }
            // Else: other types -> holeX remains null
    
            // --- 4. Store Results (only if X was calculated) ---
            if ($holeX !== null) {
                $bottomHole = ['x' => $holeX, 'y' => $bottomY];
                $upperHole = ['x' => $holeX, 'y' => $upperY];
            }
            $calculatedCoordinates[] = [
                'bottomHole' => $bottomHole,
                'upperHole' => $upperHole,
                'diameter' => ($holeX !== null) ? $doorHandleD : null
            ];
    
            // --- 5. Accumulate Height Offset ---
            $cumulativeHeightOffset += $doorsHeight + $verticalGapBetweenOpenings;
    
        } // End foreach
    
        return $calculatedCoordinates;
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

    private function DXFighterGenerator($openings) {
        $dxf = new DXFighter();
        // Use count() for better compatibility if $openings might be a plain array
        $openingCount = count($openings);
        $entitiesAdded = 0; // Counter for debugging/validation
    
        foreach ($openings as $index => $opening) {
            // --- 1. Add Polylines for the opening boundaries ---
            if (!isset($opening['coordinates']) || !is_array($opening['coordinates'])) {
                Log::warning("DXFFighterGenerator: Opening at index $index is missing 'coordinates' array. Skipping polyline generation for this opening.");
                continue; // Skip to the next opening if coordinates are missing
            }
    
            foreach ($opening['coordinates'] as $coordSetIndex => $coordinateSet) {
                // Ensure the required points exist in the coordinate set
                if (!isset($coordinateSet['P0'], $coordinateSet['P1'], $coordinateSet['P2'], $coordinateSet['P3'])) {
                    Log::warning("DXFFighterGenerator: Coordinate set $coordSetIndex in opening $index is missing points (P0-P3). Skipping this polyline.");
                    continue; // Skip this specific polyline
                }
    
                $polyline = new Polyline();
                $polyline->setFlag(0, 1); // Flag 1 typically means a closed polyline
                $polyline->setColor(140); // Set color index (e.g., 140 is often cyan/blue)
    
                // Add vertices to form a closed shape (assuming rectangular)
                $polyline->addPoint($coordinateSet['P0']);
                $polyline->addPoint($coordinateSet['P1']);
                $polyline->addPoint($coordinateSet['P2']);
                $polyline->addPoint($coordinateSet['P3']);
                $polyline->addPoint($coordinateSet['P0']); // Close the polyline by adding the start point again
    
                $dxf->addEntity($polyline);
                $entitiesAdded++;
            }
    
            // --- 2. Add Door Handle Holes (if applicable and valid) ---
            $holes = $opening['doorHandleHolesCoordinates'] ?? null; // Use null coalescing
    
            // Validate that hole data structure is complete before proceeding
            $hasValidHoleData = $holes !== null &&
                               isset($holes['bottomHole']['x'], $holes['bottomHole']['y']) &&
                               isset($holes['upperHole']['x'], $holes['upperHole']['y']) &&
                               isset($holes['diameter']);
    
            if ($hasValidHoleData) {
                // Determine conditions for adding holes
                $openingType = $opening['type'] ?? null; // Get type safely
                $isLeftOpening = ($openingType === 'left');
                $isRightOpening = ($openingType === 'right');
    
                // Add holes only if it's the *first* 'left' opening OR the *last* 'right' opening
                if (($isLeftOpening) || ($isRightOpening)) {
    
                    // Ensure the reference starting point exists
                    if (isset($opening['coordinates'][0]['P0'])) {
                        $starting_point = $opening['coordinates'][0]['P0']; // Base coordinates for holes relative to P0 of the first shape
                        $radius = $holes['diameter'] / 2;
    
                        // Calculate absolute center points for the circles
                        $bottomHoleCenter = [
                            $starting_point[0] + $holes['bottomHole']['x'],
                            $starting_point[1] + $holes['bottomHole']['y'],
                            0 // Assuming Z=0 for 2D DXF
                        ];
                        $upperHoleCenter = [
                            $starting_point[0] + $holes['upperHole']['x'],
                            $starting_point[1] + $holes['upperHole']['y'],
                            0 // Assuming Z=0
                        ];
                        
                        Log::info("DXFFighterGenerator: Starting point: " . json_encode($starting_point));
                        Log::info("DXFFighterGenerator: Bottom hole center: " . json_encode($bottomHoleCenter));
                        Log::info("DXFFighterGenerator: Upper hole center: " . json_encode($upperHoleCenter));
    
                        // Create Circle entities
                        $circle1 = new Circle($bottomHoleCenter, $radius);
                        $circle2 = new Circle($upperHoleCenter, $radius);
                        // Optional: Set color or other properties for circles if needed
                        // $circle1->setColor(COLOR_RED);
                        // $circle2->setColor(COLOR_RED);
    
                        $dxf->addEntity($circle1);
                        $dxf->addEntity($circle2);
                        $entitiesAdded += 2;
    
                        Log::info("DXFFighterGenerator: Added 2 door handle holes for opening index $index (Type: $openingType).");
    
                    } else {
                        Log::warning("DXFFighterGenerator: Cannot add holes for opening index $index. Missing P0 in the first coordinate set.");
                    }
                }
            } // End if $hasValidHoleData
        } // End foreach $openings
    
        if ($entitiesAdded === 0) {
             Log::warning("DXFFighterGenerator: No entities were added to the DXF file. Check input data.");
             // Decide how to handle this - return an error response or an empty file?
             // Returning an error might be safer:
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
            Log::info("DXFFighterGenerator: Successfully saved DXF file to $filePath");
    
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
}
