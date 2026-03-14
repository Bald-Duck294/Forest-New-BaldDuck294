<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SiteAssign;
use App\User;
use App\TourDiary;
use App\SiteDetails;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TourDiaryController extends Controller
{
    public function index(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' viewed tour diary list, User_id: ' . $user->id);

        $query = TourDiary::query();

        // Default: today's data
        if (!$request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('start_time', today());
        }

        // From Date
        if ($request->filled('from_date')) {
            $query->whereDate('start_time', '>=', $request->from_date);
        }

        // To Date
        if ($request->filled('to_date')) {
            $query->whereDate('start_time', '<=', $request->to_date);
        }

        // Laravel Pagination (10 records per page)
        $tours = $query
            ->orderBy('start_time', 'desc')
            ->paginate(10)
            ->withQueryString(); // keeps date filters during pagination

        return view('tourDiary.index', compact('tours'));
    }


    public function show($id)
    {
        $tour = TourDiary::find($id);
        return view('tourDiary.show', compact('tour'));
    }

    public function analytics(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' viewed tour diary analytics, User_id: ' . $user->id);

        /* ------------------------------
         | Date Range
         ------------------------------ */
        $from = $request->from_date ?? now()->startOfMonth()->toDateString();
        $to = $request->to_date ?? now()->toDateString();

        /* ------------------------------
         | Fetch Tours
         ------------------------------ */
        $tours = TourDiary::whereDate('start_time', '>=', $from)
            ->whereDate('start_time', '<=', $to)
            ->get();

        /* ------------------------------
         | Vehicle Normalization
         ------------------------------ */
        function normalizeVehicle($vehicle)
        {
            if (!$vehicle)
                return 'Unknown';

            $v = strtolower(trim($vehicle));

            if (Str::contains($v, ['two wheeler', '2 wheeler', '2 wheelar', 'bike'])) {
                return 'Two Wheeler';
            }

            if (Str::contains($v, ['car', 'swift', 'alto', 'baleno', 'mh'])) {
                return 'Car';
            }

            if (Str::contains($v, ['bus'])) {
                return 'Bus';
            }

            if (Str::contains($v, ['office'])) {
                return 'Office Vehicle';
            }

            return 'Other';
        }

        /* ------------------------------
         | Haversine Distance (KM)
         ------------------------------ */
        function getDistanceKm($from, $to)
        {
            if (!$from || !$to)
                return 0;

            $from = json_decode($from, true);
            $to = json_decode($to, true);

            if (!$from || !$to)
                return 0;

            $earthRadius = 6371;

            $dLat = deg2rad($to['lat'] - $from['lat']);
            $dLon = deg2rad($to['lng'] - $from['lng']);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($from['lat'])) * cos(deg2rad($to['lat'])) *
                sin($dLon / 2) * sin($dLon / 2);

            return round($earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a))), 2);
        }

        /* ------------------------------
         | Vehicle Stats
         ------------------------------ */
        $vehicleStats = $tours
            ->map(fn($t) => normalizeVehicle($t->vehicle))
            ->countBy()
            ->sortDesc();

        /* ------------------------------
         | Tours Per Day
         ------------------------------ */
        $toursPerDay = $tours
            ->groupBy(fn($t) => Carbon::parse($t->start_time)->format('d M'))
            ->map->count();

        /* ------------------------------
         | Completed vs Incomplete
         ------------------------------ */
        $completedTours = $tours->whereNotNull('end_time')->count();
        $incompleteTours = $tours->whereNull('end_time')->count();

        /* ------------------------------
         | Distance Analytics
         ------------------------------ */
        $distances = $tours->map(
            fn($t) =>
            getDistanceKm($t->from_location, $t->to_location)
        )->filter();

        $totalDistance = round($distances->sum(), 2);
        $averageDistance = $distances->count() ? round($distances->avg(), 2) : 0;

        /* ------------------------------
         | KPIs
         ------------------------------ */
        $totalTours = $tours->count();
        $uniqueUsers = $tours->pluck('user_id')->unique()->count();
        $vehiclesUsed = $vehicleStats->count();

        return view('tourDiary.analytics', compact(
            'from',
            'to',
            'totalTours',
            'completedTours',
            'incompleteTours',
            'uniqueUsers',
            'vehiclesUsed',
            'vehicleStats',
            'toursPerDay',
            'totalDistance',
            'averageDistance'
        ));
    }
}