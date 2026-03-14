<?php

namespace App\Http\Controllers;

use App\Models\BeatFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeatFeatureController extends Controller
{
    public function dashboard(Request $request)
    {
        $authUser = session('user');
        $companyId = $authUser ? $authUser->company_id : 46;

        // 1. Core KPIs (Computed directly via SQL to prevent memory exhaustion)
        $kpis = [
            'total' => BeatFeature::where('company_id', $companyId)->count(),
            'fire_points' => BeatFeature::where('company_id', $companyId)->whereIn('layer_type', ['fire_point', 'fire_lines'])->count(),
            'forest_land' => BeatFeature::where('company_id', $companyId)->where('layer_type', 'revenue_forest_land')->count(),
            'drainage' => BeatFeature::where('company_id', $companyId)->where('layer_type', 'drainage')->count(),
        ];

        // 2. Map Data (Stratified Sampling)
        // We fetch a maximum of 1500 recent features PER layer type. 
        // This keeps the map fast (under ~10k items) while ensuring all categories are visible.
        $mapFeatures = collect();
        $layerTypes = BeatFeature::where('company_id', $companyId)->select('layer_type')->distinct()->pluck('layer_type');

        foreach ($layerTypes as $type) {
            $sampledData = BeatFeature::select('id', 'layer_type', 'name', 'geometry_type', 'coordinates', 'attributes', 'site_id')
                ->where('company_id', $companyId)
                ->where('layer_type', $type)
                ->orderBy('id', 'desc') // Fetch the most recent ones
                ->limit(500) // Adjust limit to tune browser performance
                ->get();

            $mapFeatures = $mapFeatures->merge($sampledData);
        }

        // 3. Analytics: Layer Distribution (For Doughnut Chart - Uses 100% of data)
        $layerDistribution = BeatFeature::select('layer_type', DB::raw('count(*) as total'))
            ->where('company_id', $companyId)
            ->groupBy('layer_type')
            ->pluck('total', 'layer_type')
            ->toArray();

        // 4. Analytics: Features added over time (For Line Chart - Uses 100% of data)
        $timelineData = BeatFeature::select(DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'), DB::raw('count(*) as total'))
            ->where('company_id', $companyId)
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)') // Orders sequentially by date
            ->pluck('total', 'month')
            ->toArray();

        // 5. Analytics: Top Active Sites (For Bar Chart - Uses 100% of data)
        $topSites = BeatFeature::select('site_id', DB::raw('count(*) as total'))
            ->where('company_id', $companyId)
            ->whereNotNull('site_id')
            ->groupBy('site_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'site_id')
            ->toArray();

        return view('beat_features.dashboard', compact('mapFeatures', 'kpis', 'layerDistribution', 'timelineData', 'topSites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'layer_type' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'geometry_type' => 'required|string|max:20',
            'coordinates' => 'required|string',
            'attributes' => 'nullable|string',
            'site_id' => 'nullable|integer',
            'geofence_id' => 'nullable|integer',
        ]);

        if (!is_array(json_decode($validated['coordinates'], true))) {
            return back()->withErrors(['coordinates' => 'Coordinates must be a valid JSON array format.'])->withInput();
        }

        $authUser = session('user');
        $validated['company_id'] = $authUser ? $authUser->company_id : 46;
        $validated['created_at'] = now();

        BeatFeature::create($validated);

        return redirect()->back()->with('success', 'Feature added successfully.');
    }
}