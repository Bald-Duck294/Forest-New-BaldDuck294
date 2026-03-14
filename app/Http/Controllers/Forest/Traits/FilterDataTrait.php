<?php

namespace App\Http\Controllers\Forest\Traits;

use Illuminate\Support\Facades\DB;

trait FilterDataTrait
{
    /**
     * Resolve Range → Beat → User → SITE IDs
     * (Kept for compatibility, though site filtering might not depend on user selection directly
     * unless we want to show sites assigned to that user.
     * For now, we filter DATA by user_id separately.)
     */
    protected function resolveSiteIds(): array
    {
        $user = session('user');
        if (!$user)
            return [];
        $q = DB::table('site_details')->where('site_details.company_id', $user->company_id)->select('site_details.id');

        // Range → client_details.id
        if (request()->filled('range')) {
            $q->where('site_details.client_id', request('range'));
        }

        // Beat → site_details.id
        if (request()->filled('beat')) {
            $q->where('site_details.id', request('beat'));
        }

        return $q->pluck('id')->toArray();
    }

    /**
     * Apply filters safely to ANY query that has site_id and user_id
     */
    protected function applyCanonicalFilters($query, string $dateColumn = null, string $siteColumn = 'site_id', string $userColumn = 'user_id')
    {
        // Date
        if ($dateColumn) {
            if (request()->filled('start_date')) {
                $query->whereDate($dateColumn, '>=', request('start_date'));
            }
            if (request()->filled('end_date')) {
                $query->whereDate($dateColumn, '<=', request('end_date'));
            }
        }

        // Site filter (Range/Beat)
        if (request()->filled('range') || request()->filled('beat')) {
            $siteIds = $this->resolveSiteIds();

            if (empty($siteIds)) {
                // HARD STOP – prevents silent empty bugs
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn($siteColumn, $siteIds);
            }
        }

        // User Filter
        if (request()->filled('user')) {
            $query->where($userColumn, request('user'));
        }

        return $query;
    }

    /**
     * Data for global filter dropdowns
     */
    public function filterData(): array
    {
        // 1. Ranges
        $user = session('user');
        if (!$user) {
            return [];
        }
        $ranges = DB::table('client_details')
            ->where('company_id', $user->company_id)
            ->where('isActive', 1)
            ->orderBy('name')
            ->pluck('name', 'id');

        // 2. Beats (Depend on Range)
        $beats = collect();
        if (request()->filled('range')) {
            $beats = DB::table('site_details')
                ->where('company_id', $user->company_id)
                ->where('client_id', request('range'))
                ->orderBy('name')
                ->pluck('name', 'id');
        }

        // 3. Users (Depend on Beat OR Range)
        // logic:
        // - if beat selected: show users assigned to that beat
        // - if range selected (and beat not selected or "All"): show users assigned to that range
        $users = collect();

        if (request()->filled('beat')) {
            // Users in this specific Beat
            $users = DB::table('site_assign')
                ->join('users', 'users.id', '=', 'site_assign.user_id')
                ->where('site_assign.company_id', $user->company_id)
                ->whereRaw('FIND_IN_SET(?, site_assign.site_id)', [request('beat')])
                ->where('users.isActive', 1)
                ->orderBy('users.name')
                ->distinct()
                ->pluck('users.name', 'users.id');

        } elseif (request()->filled('range')) {
            // Users in this Range (Client) - regardless of beat
            $users = DB::table('site_assign')
                ->join('users', 'users.id', '=', 'site_assign.user_id')
                ->where('site_assign.client_id', request('range'))
                ->where('site_assign.company_id', $user->company_id)
                ->where('users.isActive', 1)
                ->orderBy('users.name')
                ->distinct()
                ->pluck('users.name', 'users.id');
        }

        return compact('ranges', 'beats', 'users');
    }
}
