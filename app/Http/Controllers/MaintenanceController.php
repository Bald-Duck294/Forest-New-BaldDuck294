<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MaintenanceQr;
use App\MaintenanceRequest;

use DateTime;
use DateTimeZone;
use Log;

class MaintenanceController extends Controller
{
    public function index()
    {
        $user = session('user');
        if ($user) {
            Log::info($user->name . ' view maintenance list, User_id: ' . $user->id);
            if ($user->role_id == '1') {
                $maintenances = MaintenanceRequest::where('company_id', $user->company_id)->with(['requested_by', 'qr', 'block', 'floor', 'building', 'site', 'client'])->orderBy('id', 'DESC')->get();
            } elseif ($user->role_id == '1') {
                $site_assign = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = [];
                if ($site_assign) {
                    $siteArray = json_decode($site_assign->site_id, true);
                }
                $maintenances = MaintenanceRequest::whereIn('site_id', $siteArray)->with(['requested_by', 'qr', 'block', 'floor', 'building', 'site', 'client'])->orderBy('id', 'DESC')->get();
            } else {
                $maintenances = MaintenanceRequest::where('client_id', $user->client_id)->with(['requested_by', 'qr', 'block', 'floor', 'building', 'site', 'client'])->orderBy('id', 'DESC')->get();
            }
            return view('maintenance.index')->with('maintenances', $maintenances);
        }
    }


    public function resolveMaintenance(Request $request)
    {
        // dd($request->all());
        $user = session('user');
        MaintenanceRequest::find($request->id)->update(['completed_by' => $user->id, 'completion_remark' => $request->remark, 'is_completed' => 1]);

        return true;
    }
}
