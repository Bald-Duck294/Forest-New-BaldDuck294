<?php

namespace App\Http\Controllers;

use App\SiteAssign;
use App\ClientDetails;
use App\SiteDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WebBoundaryController extends Controller
{
    // The boundary data was imported under company_id 46
    const BOUNDARY_COMPANY_ID = 46;

    public function index(Request $request)
    {
        return $this->renderMapView($request, 'forest.boundary-hierarchy');
    }

    public function normalIndex(Request $request)
    {
        return $this->renderMapView($request, 'normal.boundary-hierarchy');
    }

    protected function renderMapView(Request $request, $viewName)
    {
        $authUser = Session::get('user');
        if (!$authUser) {
            return redirect('/login');
        }




        

        $rangeId = $request->range_id;
        $siteId = $request->site_id;
        $year = $request->year;

        $availableRanges = [];
        $availableSections = [];
        $availableBeats = [];
        $availableYears = ['2023-24', '2024-25'];

        // Fetch boundary hierarchy ranges, sections, beats for filters
        $allRanges = DB::table('boundary_hierarchies')
            ->where('company_id', $authUser->company_id)
            ->where('level', 'range')
            ->get(['id', 'name']);
        $availableRanges = $allRanges;

        if ($rangeId) {
            $availableSections = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->where('level', 'section')
                ->where('parent_id', $rangeId)
                ->get(['id', 'name']);
        }

        $sectionId = $request->section_id;
        if ($sectionId) {
            $availableBeats = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->where('level', 'beat')
                ->where('parent_id', $sectionId)
                ->get(['id', 'name']);
        } elseif ($rangeId) {
            // Get all beats under this range (through sections)
            $sectionIds = DB::table('boundary_hierarchies')
                ->where('parent_id', $rangeId)->where('level', 'section')
                ->pluck('id');
            $availableBeats = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->where('level', 'beat')
                ->whereIn('parent_id', $sectionIds)
                ->get(['id', 'name']);
        }

        return view($viewName, [
            'availableRanges' => $availableRanges,
            'availableSections' => $availableSections,
            'availableBeats' => $availableBeats,
            'availableYears' => $availableYears,
            'selectedRange' => $rangeId,
            'selectedSection' => $sectionId,
            'selectedBeat' => $siteId,
            'selectedYear' => $year,
            'userRole' => $authUser->role_id,
        ]);
    }

    public function getSections($rangeId)
    {
        $authUser = Session::get('user');
        if (!$authUser)
            return response()->json([]);
        return response()->json(
            DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->where('level', 'section')
                ->where('parent_id', $rangeId)
                ->get(['id', 'name'])
        );
    }

    public function getBeats($sectionId)
    {
        $authUser = Session::get('user');
        if (!$authUser)
            return response()->json([]);
        return response()->json(
            DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->where('level', 'beat')
                ->where('parent_id', $sectionId)
                ->get(['id', 'name'])
        );
    }

    public function getMapData(Request $request)
    {
        $authUser = Session::get('user');
        if (!$authUser) {
            return response()->json(['status' => 'FAILURE', 'message' => 'Unauthorized'], 401);
        }

        $rangeId = $request->range_id;
        $sectionId = $request->section_id;
        $siteId = $request->site_id; // beat_id
        $onlyCounts = $request->boolean('only_counts');
        $layerTypesRequested = $request->layer_types;
        $counts = [];
        $data = [];

        // Scoping: use boundary_hierarchies IDs directly (range/section/beat are hierarchy IDs)
        $allDescendantIds = [];

        if ($siteId) {
            // Beat selected - just that beat
            $allDescendantIds[] = (int)$siteId;
        } elseif ($sectionId) {
            // Section selected - section + all its beats
            $allDescendantIds[] = (int)$sectionId;
            $beats = DB::table('boundary_hierarchies')->where('parent_id', $sectionId)->pluck('id')->toArray();
            $allDescendantIds = array_merge($allDescendantIds, $beats);
        } elseif ($rangeId) {
            // Range selected - range + sections + beats
            $allDescendantIds[] = (int)$rangeId;
            $sections = DB::table('boundary_hierarchies')->where('parent_id', $rangeId)->get();
            foreach ($sections as $sec) {
                $allDescendantIds[] = $sec->id;
                $beats = DB::table('boundary_hierarchies')->where('parent_id', $sec->id)->pluck('id')->toArray();
                $allDescendantIds = array_merge($allDescendantIds, $beats);
            }
        }

        // If no specific scope, load ALL boundaries for the company
        if (empty($allDescendantIds)) {
            $allDescendantIds = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->pluck('id')->toArray();
        }

        // Administrative Boundaries Layer Request
        if ($layerTypesRequested && in_array('administrative_boundaries', $layerTypesRequested)) {
            $adminFeatures = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->whereIn('id', $allDescendantIds)
                ->get();


            foreach ($adminFeatures as $feat) {
                $normalizedCoords = $this->normalizeCoordinates('Polygon', $feat->coordinates);
                if (empty($normalizedCoords))
                    continue;

                $data['administrative_boundaries'][] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => $normalizedCoords,
                    ],
                    'properties' => array_merge(json_decode($feat->metadata, true) ?? [], [
                        'id' => $feat->id,
                        'name' => $feat->name,
                        'layer_type' => 'administrative_boundaries',
                        'level' => $feat->level
                    ])
                ];
            }
        }

        // Other Features
        $query = DB::table('boundary_features')->where('company_id', $authUser->company_id);
        if (!empty($allDescendantIds)) {
            $query->whereIn('boundary_id', $allDescendantIds);
        }

        if ($layerTypesRequested && is_array($layerTypesRequested)) {
            $filteredLayers = array_filter($layerTypesRequested, function ($lt) {
                return $lt !== 'administrative_boundaries';
            });
            if (count($filteredLayers) > 0) {
                $query->whereIn('type', $filteredLayers);
            } else {
                // If only administrative_boundaries requested, dummy query to avoid getting all features
                $query->where('id', -1);
            }
        }

        if ($onlyCounts) {
            $counts = $query->select('type', DB::raw('count(*) as aggregate'))
                ->groupBy('type')
                ->get()
                ->pluck('aggregate', 'type')->toArray();

            // Add count for administrative bounds
            $adminCount = DB::table('boundary_hierarchies')
                ->where('company_id', $authUser->company_id)
                ->whereIn('id', $allDescendantIds)
                ->count();
            $counts['administrative_boundaries'] = $adminCount;

            return response()->json($this->cleanUtf8([
                'status' => 'SUCCESS',
                'counts' => $counts,
            ]));
        }

        // Get features
        $features = $query->get();
        foreach ($features as $feature) {
            $normalizedCoords = $this->normalizeCoordinates($feature->geometry_type, $feature->coordinates);

            if (empty($normalizedCoords))
                continue;

            $attrs = $feature->metadata ? json_decode($feature->metadata, true) : [];

            $data[$feature->type][] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => $feature->geometry_type,
                    'coordinates' => $normalizedCoords,
                ],
                'properties' => array_merge($attrs ?? [], [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'layer_type' => $feature->type,
                ])
            ];
        }

        return response()->json($this->cleanUtf8([
            'status' => 'SUCCESS',
            'counts' => $counts,
            'data' => $data,
        ]));
    }

    private function cleanUtf8($data)
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanUtf8($value);
            }
            return $cleaned;
        } elseif (is_object($data)) {
            if ($data instanceof \Illuminate\Support\Collection) {
                return $data->map(function ($item) {
                    return $this->cleanUtf8($item);
                });
            }
            $cleaned = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                $cleaned->$key = $this->cleanUtf8($value);
            }
            return $cleaned;
        }
        return $data;
    }

    protected function isPointCoords($item)
    {
        return is_array($item) && (
            (isset($item[0]) && !is_array($item[0])) ||
            isset($item['lat']) || isset($item['lng'])
        );
    }

    protected function normalizeCoordinates($type, $coords)
    {
        if (empty($coords))
            return [];
        if (is_string($coords)) {
            $decoded = json_decode($coords, true);
            if ($decoded !== null) {
                $coords = $decoded;
            } else {
                // Try KML-style format: "lng,lat,alt lng,lat,alt ..."
                $coords = $this->parseKmlCoordinates($coords);
            }
        }
        if (!is_array($coords))
            return [];

        if ($type === 'Point') {
            if (isset($coords[0]) && is_array($coords[0])) {
                $coords = $coords[0];
            }
            return $this->swapLatLog($coords);
        }

        if ($type === 'LineString' || $type === 'MultiPoint') {
            return array_map([$this, 'swapLatLog'], $coords);
        }

        if ($type === 'Polygon' || $type === 'MultiLineString') {
            if (isset($coords[0]) && $this->isPointCoords($coords[0])) {
                $coords = [$coords];
            }
            return array_map(function ($ring) {
                if (!is_array($ring))
                    return [];
                return array_map([$this, 'swapLatLog'], $ring);
            }, $coords);
        }

        if ($type === 'MultiPolygon') {
            if (isset($coords[0])) {
                if ($this->isPointCoords($coords[0])) {
                    // It's a single ring, wrap to Polygon and then to MultiPolygon
                    $coords = [[$coords]];
                } elseif (isset($coords[0][0]) && $this->isPointCoords($coords[0][0])) {
                    // It's a single polygon (array of rings), wrap to MultiPolygon
                    $coords = [$coords];
                }
            }
            return array_map(function ($polygon) {
                if (!is_array($polygon))
                    return [];
                return array_map(
                    function ($ring) {
                        if (!is_array($ring))
                            return [];
                        return array_map([$this, 'swapLatLog'], $ring);
                    },
                    $polygon
                );
            }, $coords);
        }

        return $coords;
    }

    /**
     * Parse KML-style coordinate string: "lng,lat,alt lng,lat,alt ..."
     * Returns array of [lng, lat] pairs.
     */
    protected function parseKmlCoordinates($coordString)
    {
        $coordString = trim($coordString);
        if (empty($coordString))
            return [];

        $points = preg_split('/\s+/', $coordString);
        $result = [];
        foreach ($points as $point) {
            $parts = explode(',', $point);
            if (count($parts) >= 2) {
                $result[] = [floatval($parts[0]), floatval($parts[1])];
            }
        }
        return $result;
    }

    protected function swapLatLog($pair)
    {
        if (!is_array($pair))
            return $pair;

        $val1 = 0;
        $val2 = 0;

        if (isset($pair['lat']) && isset($pair['lng'])) {
            // Already an associative array, convert to numerically indexed [lng, lat]
            $val1 = floatval($pair['lng']);
            $val2 = floatval($pair['lat']);
        } elseif (isset($pair[0]) && isset($pair[1])) {
            $val1 = floatval($pair[0]);
            $val2 = floatval($pair[1]);
        } else {
            return $pair; // Unknown format
        }

        // Fix swapped coordinates (India's longitude is > 60, latitude is < 45)
        if ($val1 < 45 && $val2 > 60) {
            $temp = $val1;
            $val1 = $val2;
            $val2 = $temp;
        }

        return [$val1, $val2];
    }
}
