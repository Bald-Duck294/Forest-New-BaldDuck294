<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\MaintenanceQR;
use App\MaintenanceRequest;

use DateTime;
use DateTimeZone;
use Log;

class MaintenanceQRController extends Controller
{
    public function index($block_id)
    {
        // dd($id);
        $qrs = MaintenanceQR::where('block_id', $block_id)->get();

        return view('maintenanceqr.index', compact('qrs', 'block_id'));
    }


    public function resolveMaintenance(Request $request)
    {
        // dd($request->all());
        $user = session('user');
        MaintenanceRequest::find($request->id)->update(['completed_by' => $user->id, 'completion_remark' => $request->remark, 'is_completed' => 1]);

        return true;
    }
}
