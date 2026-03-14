<?php

namespace App\Http\Controllers\Forest;

use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    // Legacy methods kept to prevent route errors if cached, but effectively disabled/unused.

    public function beats($rangeId)
    {
        return [];
    }

    public function compartments($beatId)
    {
        return [];
    }
}
