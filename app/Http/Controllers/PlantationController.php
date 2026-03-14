<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantation;
use App\Models\ObservationRecord;
use App\Models\SiteDetail;
use App\Models\User;

class PlantationController extends Controller
{
    protected $phases = [
        'identification',
        'measurement',
        'planning',
        'planting',
        'fencing',
        'observation',
    ];

    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $plantations = Plantation::with('site')->latest()->get();
        return view('plantation.dashboard', compact('plantations'));
    }

    // ─── WORKFLOW VIEW ────────────────────────────────────────────────────────

    public function workflow($id)
    {
        $plantation = Plantation::with(['site', 'observations'])->findOrFail($id);
        $phases = $this->phases;

        return view('plantation.workflow', compact('plantation', 'phases'));
    }



    // ─── SAVE PHASE DATA ──────────────────────────────────────────────────────

    public function saveWorkflow(Request $request, $id)
    {
        $plantation = Plantation::findOrFail($id);
        $phases = $this->phases;
        $currentPhase = $plantation->current_phase;
        $currentIndex = array_search($currentPhase, $phases);

        // Map request data to plantation fields per phase
        switch ($currentPhase) {

            case 'identification':
                // No dedicated DB fields — just advance to next phase
                break;

            case 'measurement':
                $plantation->area = $request->input('area');
                $plantation->soil_type = $request->input('soil_type');
                break;

            case 'planning':
                $plantation->plant_species = $request->input('plant_species');
                $plantation->plant_count = $request->input('plant_count');
                break;

            case 'planting':
                $plantation->plantation_start_date = $request->input('plantation_start_date');
                $plantation->plantation_end_date = $request->input('plantation_end_date');
                break;

            case 'fencing':
                $plantation->is_fenced = $request->has('is_fenced') ? 1 : 0;
                break;

            case 'observation':
                ObservationRecord::create([
                    'plantation_id' => $plantation->id,
                    'observation_date' => $request->input('observation_date', now()->format('Y-m-d')),
                    'inspector_id' => auth()->id(),
                    'remarks' => $request->input('remarks'),
                ]);
                // Observation phase repeats — do NOT advance; plantation stays active
                $plantation->save();
                return redirect()->route('plantation.workflow', $plantation->id)
                    ->with('success', 'Observation recorded successfully.');
        }

        // Advance to next phase
        if ($currentIndex !== false && isset($phases[$currentIndex + 1])) {
            $plantation->current_phase = $phases[$currentIndex + 1];
        }
        else {
            // Already at last phase (shouldn't reach here for 'observation' — caught above)
            $plantation->status = 'completed';
        }

        $plantation->save();

        return redirect()->route('plantation.workflow', $plantation->id)
            ->with('success', 'Phase saved successfully.');
    }

    public function create()
    {
        $sites = SiteDetail::orderBy('name')->get();

        $lastPlantation = Plantation::latest()->first();

        if ($lastPlantation) {
            $number = (int)substr($lastPlantation->code, 3) + 1;
        }
        else {
            $number = 1;
        }

        $nextCode = 'PLT' . str_pad($number, 3, '0', STR_PAD_LEFT);

        return view('plantation.create', compact('sites', 'nextCode'));
    }

    public function show($id)
    {
        $plantation = Plantation::with(['site', 'user', 'observations'])->findOrFail($id);

        return view('plantation.show', compact('plantation'));
    }

    // ─── CREATE (store new plantation) ────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        // Get last plantation
        $lastPlantation = Plantation::latest()->first();

        if ($lastPlantation) {
            $number = (int)substr($lastPlantation->code, 3) + 1;
        }
        else {
            $number = 1;
        }

        $code = 'PLT' . str_pad($number, 3, '0', STR_PAD_LEFT);

        $plantation = Plantation::create([
            'code' => $code,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'site_id' => $request->input('site_id'),
            'user_id' => auth()->id(),
            'current_phase' => 'identification',
            'status' => 'active',
        ]);

        return redirect()->route('plantation.workflow', $plantation->id)
            ->with('success', 'Plantation created. Start the workflow.');
    }



    // ─── ANALYTICS ───────────────────────────────────────────────────────────

    public function analytics()
    {
        $plantations = Plantation::with('observations')->get();

        $totalPlantations = $plantations->count();

        $optimal = 0;
        $actionRequired = 0;
        $critical = 0;

        foreach ($plantations as $pln) {

            $observations = $pln->observations->count();

            if ($observations >= 5) {
                $optimal++;
            }
            elseif ($observations >= 2) {
                $actionRequired++;
            }
            else {
                $critical++;
            }
        }

        // Monthly observations (line chart)
        $monthlyObservations = ObservationRecord::selectRaw('MONTH(observation_date) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total');

        // Top performing plantations
        $topPlantations = Plantation::withCount('observations')
            ->orderByDesc('observations_count')
            ->limit(5)
            ->get();

        // Example utilization metrics (until you build real resource tables)
        $waterUsage = rand(60, 95);
        $fertilizerStock = rand(30, 70);
        $laborEfficiency = rand(70, 98);

        return view('plantation.analytics', [
            'totalPlantations' => $totalPlantations,
            'optimal' => $optimal,
            'actionRequired' => $actionRequired,
            'critical' => $critical,
            'monthlyObservations' => $monthlyObservations,
            'topPlantations' => $topPlantations,
            'waterUsage' => $waterUsage,
            'fertilizerStock' => $fertilizerStock,
            'laborEfficiency' => $laborEfficiency
        ]);
    }
}
