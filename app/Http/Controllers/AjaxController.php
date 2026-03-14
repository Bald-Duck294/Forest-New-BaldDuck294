<?php

namespace App\Http\Controllers;
use App\SiteDetails;
use App\SiteAssign;

class AjaxController extends Controller
{
    public function clientSites($client_id)
    {
        $sites = SiteDetails::where('client_id', $client_id)->orderBy('name', 'asc')->get();
        return response()->json($sites);
    }

    public function clientUsers($client_id)
    {
        $users = SiteAssign::where('client_id', $client_id)
            // ->join('users', 'users.id', '=', 'site_assign.user_id')
            ->select('user_id as id', 'user_name as name')
            ->distinct()
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($users);
    }


    public function siteUsers($site_id)
    {
        $users = SiteAssign::where('site_id', $site_id)
            // ->join('users', 'users.id', '=', 'site_assign.user_id')
            ->select('user_id as id', 'user_name as name')
            ->distinct()
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($users);
    }
}