<?php

namespace App\Http\Controllers;

use App\Models\BeatKmlFeature;
use App\SiteAssign;
use App\Users;
use App\ClientDetails;
use App\SiteDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BeatMapController extends Controller
{
    public function forestIndex(Request $request)
    {
        return $this->renderMapView($request, 'forest.know-your-area');
    }

    public function normalIndex(Request $request)
    {
        return $this->renderMapView($request, 'normal.know-your-area');
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
        $availableBeats = [];

        // FIX 1: Add ->values()->toArray() so JavaScript doesn't treat this as an Object
        $availableYears = BeatKmlFeature::where('company_id', $authUser->company_id)
            ->whereNotNull('created_at')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->pluck('year')
            ->values()
            ->toArray();

        if ($authUser->role_id == 1) {
            // Superadmin: All ranges
            $availableRanges = ClientDetails::where('company_id', $authUser->company_id)->get(['id', 'name']);
            if ($rangeId) {
                $availableBeats = SiteDetails::where('client_id', $rangeId)->get(['id', 'name']);
            }
        } elseif ($authUser->role_id == 7) {
            // Admin/Client: Assigned ranges
            $assign = SiteAssign::where('user_id', $authUser->id)->first();
            if ($assign && $assign->client_id) {
                $clientIds = is_array(json_decode($assign->client_id, true)) ? json_decode($assign->client_id, true) : [$assign->client_id];
                $availableRanges = ClientDetails::whereIn('id', $clientIds)->get(['id', 'name']);
                if ($rangeId && in_array($rangeId, $clientIds)) {
                    $availableBeats = SiteDetails::where('client_id', $rangeId)->get(['id', 'name']);
                }
            }
        } elseif ($authUser->role_id == 2) {
            // Supervisor: Assigned beats
            $assign = SiteAssign::where('user_id', $authUser->id)->first();
            if ($assign && $assign->site_id) {
                $siteIds = is_array(json_decode($assign->site_id, true)) ? json_decode($assign->site_id, true) : [$assign->site_id];
                $availableBeats = SiteDetails::whereIn('id', $siteIds)->get(['id', 'name']);

                // For supervisor, we need to know the range of their beats
                $rangeIds = SiteDetails::whereIn('id', $siteIds)->distinct()->pluck('client_id');
                $availableRanges = ClientDetails::whereIn('id', $rangeIds)->get(['id', 'name']);

                if ($rangeId) {
                    $availableBeats = SiteDetails::whereIn('id', $siteIds)->where('client_id', $rangeId)->get(['id', 'name']);
                }
            }
        } elseif ($authUser->role_id == 3) {
            // Guard: Assigned beat
            $assign = SiteAssign::where('user_id', $authUser->id)->first();
            if ($assign && $assign->site_id) {
                $siteId = $assign->site_id; // Lock to their site
                $availableBeats = SiteDetails::where('id', $siteId)->get(['id', 'name']);

                $beat = SiteDetails::find($siteId);
                if ($beat) {
                    $rangeId = $beat->client_id;
                    $availableRanges = ClientDetails::where('id', $rangeId)->get(['id', 'name']);
                }
            }
        }

        return view($viewName, [
            'availableRanges' => $availableRanges,
            'availableBeats' => $availableBeats,
            'availableYears' => $availableYears,
            'selectedRange' => $rangeId,
            'selectedBeat' => $siteId,
            'selectedYear' => $year,
            'userRole' => $authUser->role_id,
        ]);
    }

    public function getMapData(Request $request)
    {
        $authUser = Session::get('user');
        if (!$authUser) {
            return response()->json(['status' => 'FAILURE', 'message' => 'Unauthorized'], 401);
        }

        $rangeId = $request->range_id;
        $siteId = $request->site_id;
        $geofenceId = $request->geofence_id;
        $year = $request->year;
        $onlyCounts = $request->boolean('only_counts');
        $layerTypesRequested = $request->layer_types; // Array of layers if provided
        $counts = [];

        $query = BeatKmlFeature::where('company_id', $authUser->company_id);

        // Role-based scoping
        if ($authUser->role_id == 2) {
            $assign = SiteAssign::where('user_id', $authUser->id)->first();
            if ($assign && $assign->site_id) {
                $scopedSiteIds = is_array(json_decode($assign->site_id, true)) ? json_decode($assign->site_id, true) : [$assign->site_id];
                if (!$siteId && !$rangeId) {
                    $query->whereIn('site_id', $scopedSiteIds);
                }
            }
        } elseif ($authUser->role_id == 3) {
            $assign = SiteAssign::where('user_id', $authUser->id)->first();
            if ($assign && $assign->site_id) {
                if (!$siteId) {
                    $query->where('site_id', $assign->site_id);
                    $siteId = $assign->site_id; // For geofences below
                }
            }
        }

        if ($siteId) {
            $query->where('site_id', $siteId);
        } elseif ($rangeId) {
            $query->where('range_id', $rangeId);
        }

        if ($geofenceId) {
            $query->where('geofence_id', $geofenceId);
        }

        if ($year) {
            $query->where(function ($q) use ($year) {
                $q->whereYear('created_at', $year)->orWhereNull('created_at');
            });
        }

        if ($layerTypesRequested && is_array($layerTypesRequested)) {
            $query->whereIn('layer_type', $layerTypesRequested);
        }

        $geofences = [];
        if ($siteId || $rangeId) {
            $qGeo = DB::table('site_geofences')->whereNull('deleted_at')->where('company_id', $authUser->company_id);
            if ($siteId) {
                $qGeo->where('site_id', $siteId);
            } else {
                $qGeo->where('client_id', $rangeId);
            }
            $geofences = $qGeo->get();
        } else {
            // Default scoping for initial load
            $qGeo = DB::table('site_geofences')->whereNull('deleted_at')->where('company_id', $authUser->company_id);
            if ($authUser->role_id == 2) {
                $assign = SiteAssign::where('user_id', $authUser->id)->first();
                if ($assign && $assign->site_id) {
                    $scopedSiteIds = is_array(json_decode($assign->site_id, true)) ? json_decode($assign->site_id, true) : [$assign->site_id];
                    $qGeo->whereIn('site_id', $scopedSiteIds);
                }
            } elseif ($authUser->role_id == 3) {
                $assign = SiteAssign::where('user_id', $authUser->id)->first();
                if ($assign && $assign->site_id) {
                    $qGeo->where('site_id', $assign->site_id);
                }
            } elseif ($authUser->role_id == 7) {
                $assign = SiteAssign::where('user_id', $authUser->id)->first();
                if ($assign && $assign->client_id) {
                    $clientIds = is_array(json_decode($assign->client_id, true)) ? json_decode($assign->client_id, true) : [$assign->client_id];
                    $qGeo->whereIn('client_id', $clientIds);
                }
            } else {
                // Role 1 or fallback
            }
            $geofences = $qGeo->get();
        }

        if ($onlyCounts) {
            $counts = $query->select('layer_type', DB::raw('count(*) as aggregate'))
                ->groupBy('layer_type')
                ->get()
                ->pluck('aggregate', 'layer_type');

            return response()->json($this->cleanUtf8([
                'status' => 'SUCCESS',
                'counts' => $counts,
                'geofences' => $geofences,
            ]));
        }

        $features = $query->select('id', 'layer_type', 'name', 'geometry_type', 'coordinates', 'attributes')
            ->selectRaw('YEAR(created_at) as year')
            ->get();

        $data = [];
        foreach ($features as $feature) {
            $normalizedCoords = $this->normalizeCoordinates($feature->geometry_type, $feature->coordinates);

            if (empty($normalizedCoords))
                continue;

            $attrs = $feature->attributes;
            if (is_string($attrs)) {
                $attrs = json_decode($attrs, true) ?: [];
            } elseif (is_object($attrs)) {
                $attrs = (array)$attrs;
            }

            // FIX 2: Create a FLAT array instead of nesting it by layer_type
            $data[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => $feature->geometry_type,
                    'coordinates' => $normalizedCoords,
                ],
                'properties' => array_merge($attrs ?? [], [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'layer_type' => $feature->layer_type,
                    'year' => $feature->year
                ])
            ];
        }

        return response()->json($this->cleanUtf8([
            'status' => 'SUCCESS',
            'counts' => $counts ?? [],
            'data' => array_values($data),
            'geofences' => $geofences,
        ]));
    }

    private function cleanUtf8($data)
    {
        if (is_string($data)) {
            // Force UTF-8 encoding and replace invalid sequences
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->cleanUtf8($value);
            }
            return $cleaned;
        } elseif (is_object($data)) {
            // Handle stdClass/Collection objects
            if ($data instanceof \Illuminate\Support\Collection) {
                return $data->map(fn($item) => $this->cleanUtf8($item));
            }
            $cleaned = new \stdClass();
            foreach (get_object_vars($data) as $key => $value) {
                $cleaned->$key = $this->cleanUtf8($value);
            }
            return $cleaned;
        }
        return $data;
    }

    protected function normalizeCoordinates($type, $coords)
    {
        if (empty($coords))
            return [];

        // If stored as a string, decode it (should be handled by model cast but just in case)
        if (is_string($coords)) {
            $coords = json_decode($coords, true);
        }

        if (!is_array($coords))
            return [];

        if ($type === 'Point') {
            // Check for double nesting [[lat, lng]] and flatten to [lat, lng]
            if (count($coords) > 0 && is_array($coords[0])) {
                $coords = $coords[0];
            }
            return $this->swapLatLog($coords);
        }

        if ($type === 'LineString' || $type === 'MultiPoint') {
            return array_map([$this, 'swapLatLog'], $coords);
        }

        if ($type === 'Polygon' || $type === 'MultiLineString') {
            // GeoJSON Polygon expects list of rings: [[[lng, lat], ...], [ring2], ...]
            // If we have [[lat, lng], ...], it's a single ring and needs wrapping.
            if (count($coords) > 0 && is_array($coords[0]) && !is_array($coords[0][0])) {
                $coords = [$coords];
            }
            return array_map(function ($ring) {
                return array_map([$this, 'swapLatLog'], $ring);
            }, $coords);
        }

        if ($type === 'MultiPolygon') {
            // GeoJSON MultiPolygon: [[ [[lng, lat], ... polygons ], rings ], ...]
            // If we have [[[lat, lng], ...]], it's missing the MultiPolygon wrapper.
            if (count($coords) > 0 && is_array($coords[0]) && is_array($coords[0][0]) && !is_array($coords[0][0][0])) {
                $coords = [$coords];
            }
            return array_map(function ($polygon) {
                return array_map(
                    function ($ring) {
                        return array_map([$this, 'swapLatLog'], $ring);
                    },
                    $polygon
                );
            }, $coords);
        }

        return $coords;
    }

    protected function swapLatLog($pair)
    {
        if (!is_array($pair) || count($pair) < 2)
            return $pair;

        $val1 = floatval($pair[0]);
        $val2 = floatval($pair[1]);

        // Heuristic for India: Lat 8-37, Lng 68-97
        // If val1 is < 45 and val2 is > 60, it's [Lat, Lng], which MUST be swapped to [Lng, Lat] for GeoJSON
        if ($val1 < 45 && $val2 > 60) {
            $pair[0] = $val2;
            $pair[1] = $val1;
        }

        return $pair;
    }
}
