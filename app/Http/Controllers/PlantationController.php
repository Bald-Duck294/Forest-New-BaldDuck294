<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantation;
use App\Models\User;
use App\Models\Grid;

class PlantationController extends Controller
{
    public function dashboard()
    {
        $plantations = Plantation::all();
        return view('plantation.dashboard', compact('plantations'));
    }

    public function grids()
    {
        $grids = Grid::all();
        return view('plantation.grids', compact('grids'));
    }

    public function users()
    {
        $users = User::all();
        return view('plantation.users', compact('users'));
    }

    public function analytics()
    {
        return view('plantation.analytics');
    }

    // ADD THIS METHOD
    public function workflow($id)
    {
        $plantation = Plantation::findOrFail($id);

        $phases = [
            'identification',
            'measurement',
            'planning',
            'planting',
            'fencing',
            'observation'
        ];

        return view('plantation.workflow', compact('plantation', 'phases'));
    }
}
