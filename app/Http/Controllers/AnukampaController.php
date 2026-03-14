<?php

namespace App\Http\Controllers;

use App\Models\AnukampaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnukampaController extends Controller
{
    // --- DASHBOARD VIEW ---
    public function dashboard()
    {
        // 1. KPIs
        $kpis = [
            'total' => AnukampaRecord::count(),
            'crop' => AnukampaRecord::where('incident_type', 'Crop Damage')->count(),
            'house' => AnukampaRecord::where('incident_type', 'House Damage')->count(),
            'pending' => AnukampaRecord::where('status', 'Pending')->count(),
        ];

        // 2. Recent Database Records (Limit 5)
        $recentClaims = AnukampaRecord::orderBy('created_at', 'desc')->take(5)->get();

        // 3. Map Data (Lat, Lng, Intensity, Type)
        $mapData = AnukampaRecord::select('latitude', 'longitude', 'incident_type')
            ->get()
            ->map(function ($record) {
                // Assign a base intensity (e.g., 0.8) for the heatmap
                return [$record->latitude, $record->longitude, 0.8, strtolower(explode(' ', $record->incident_type)[0])];
            });

        // 4. Chart Data: Incidents by Range
        $chartData = AnukampaRecord::select('range', 'incident_type', DB::raw('count(*) as total'))
            ->groupBy('range', 'incident_type')
            ->get();

        return view('anukampa.dashboard', compact('kpis', 'recentClaims', 'mapData', 'chartData'));
    }

    // --- ALL CLAIMS VIEW ---
    public function index(Request $request)
    {
        $query = AnukampaRecord::query();

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('victim_name', 'LIKE', "%{$search}%")
                    ->orWhere('village_name', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Dropdown Filters
        if ($request->filled('type')) {
            $query->where('incident_type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('range')) {
            $query->where('range', $request->input('range'));
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $claims = $query->paginate(15)->appends($request->all());

        return view('anukampa.claims', compact('claims'));
    }

    // --- STORE NEW INCIDENT ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'victim_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'range' => 'required|string|max:100',
            'village_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'incident_type' => 'required|string|max:50',
            'incident_date' => 'required|date',
            'animal_responsible' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        AnukampaRecord::create($validated);

        return redirect()->back()->with('success', 'Incident logged successfully.');
    }

    // --- VIEW SINGLE CLAIM ---
    public function show($id)
    {
        $claim = AnukampaRecord::findOrFail($id);
        return view('anukampa.show', compact('claim'));
    }

    // --- EDIT CLAIM ---
    public function edit($id)
    {
        $claim = AnukampaRecord::findOrFail($id);
        return view('anukampa.edit', compact('claim'));
    }

    // --- UPDATE CLAIM ---
    public function update(Request $request, $id)
    {
        $claim = AnukampaRecord::findOrFail($id);

        $validated = $request->validate([
            'victim_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:15',
            'estimated_loss' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            // Add other fields you want editable
        ]);

        $claim->update($validated);
        return redirect()->route('anukampa.claims')->with('success', 'Claim updated successfully.');
    }

    // --- UPDATE STATUS ---
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);

        $claim = AnukampaRecord::findOrFail($id);
        $claim->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }
}