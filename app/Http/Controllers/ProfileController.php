<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Show user profile
     * TODO: Implement full profile view
     */
    public function show($user)
    {
        // For now, redirect to dashboard or return 404
        // Profile view not implemented yet
        return redirect()->route('dashboard')->with('info', 'Profile page is under development');
    }
}
