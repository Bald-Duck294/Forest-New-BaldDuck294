<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Overtime;
use App\SiteAssign;
use App\Users;
use App\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Requests\OvertimeRequest;
use DataTables;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Overtime $overtime)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Overtime $overtime)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Overtime $overtime)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Overtime $overtime)
    // {
    //     //
    // }


    private $folder = "admin.overtime.";

    public function index()
    {
        // dd('index');
        return View('admin.overtime.index', [
            'get_data' => route('admin.overtime.getData'),
        ]);
    }

    public function getData()
    {

        return View('admin.overtime.content', [
            'add_new' => route('overtime.create'),
            'getDataTable' => route('admin.overtime.getDataTable'),
            'moveToTrashAllLink' => route('admin.overtime.massDelete'),
        ]);
    }

    public function getDataTable()
    {

        $user = session('user');
        // dd($user);
        $overtimes = [];
        if ($user->role_id == 2) {
            $site_assign = SiteAssign::where('user_id', $user->id)->first();
            $overtimes = [];
            if ($site_assign) {
                $siteArray = json_decode($site_assign->site_id, true);
                $site_users = SiteAssign::whereIn('site_id', $siteArray)->pluck('user_id')->toArray();
                $overtimes = Overtime::whereIn('user_id', $site_users)->where('company_id', $user->company_id)->with('user')->get();
            }
        } else
            $overtimes = Overtime::where('company_id', $user->company_id)->with('user')->get();
        //   dd($overtimes);

        // return View('admin.overtime.content',compact('overtimes'));

        return FacadesDataTables::of($overtimes)

            ->addIndexColumn()
            ->addColumn('date', function ($data) {
                return date('d-m-Y', strtotime($data->date));
            })
            ->addColumn('employee', function ($data) {
                // dd($data, $data->user_id);
                return "<div class='row'><div class='col-md-6 col-lg-6 my-auto'><b class='mb-0'>" . $data->user->name . "</b><p class='mb-2' title='" . $data->user_id . "'><small><i class='la la-at'></i>" . $data->user->gen_id . "</small></p></div><div class='col-md-4 col-lg-4'><small class='text-muted float-right'></small></div></div>";
            })
            ->addColumn('details', function ($data) {
                return "<b>" . $data->title . "</b><p>" . $data->description . "</p>";
            })
            ->addColumn('hour', function ($data) {
                return $data->hour / 60 . " hr";
            })
            ->addColumn('action', function ($data) {
                $btn = "<div class='table-actions'><a href='" . route("overtime.edit", [$data->id]) . "'><i class='la la-edit text-dark'></i></a><a data-href='" . route("overtime.destroy", [$data->id]) . "' class='delete cursure-pointer'><i class='la la-trash text-danger'></i></a></div>";
                return $btn;
            })
            ->rawColumns(['employee', 'action', 'details'])
            ->toJson();
    }

    public function create()
    {
        $employees = Users::get();
        return View("admin.overtime.create", [
            'form_store' => route('overtime.store'),
            'employees' => $employees,
        ]);
    }

    public function store(OvertimeRequest $request)
    {
        $user = session('user');
        // dd($request,$user->company_id);
        $data = [
            'title' => $request->title,
            'company_id' => $user->company_id,
            'description' => $request->description,
            'rate_amount' => $request->rate_amount,
            'hour' => $request->hour * 60,
            'date' => $request->date,
            'user_id' => $request->employee_id,
        ];
        // dd($data);
        $overtime = Overtime::create($data);
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Overtime",
            'message' => "Overtime created by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'New Overtime added successfully.',
            'redirect_to' => route('overtime.index')
        ]);
    }

    public function show(Overtime $overtime)
    {
        abort(404);
    }

    public function edit($id)
    {

        $overtime = Overtime::find($id);
        $employees = Users::get();
        return View('admin.overtime.edit', [
            'overtime' => $overtime,
            'form_update' => route('overtime.update', ['overtime' => $overtime]),
            'employees' => $employees,
        ]);
    }

    public function update(OvertimeRequest $request, Overtime $overtime)
    {
        $user = session('user');
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'rate_amount' => $request->rate_amount,
            'hour' => $request->hour * 60,
            'date' => $request->date,
            'user_id' => $request->employee_id,
        ];
        $overtime->update($data);
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Overtime",
            'message' => "Overtime updated by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => $overtime->title . ' updated successfully.',
            'redirect_to' => route('overtime.index')
        ]);
    }

    public function destroy($id)
    {
           $user= session('user');

        $overtime = Overtime::find($id);
        // dd($id);
        $trash = $overtime->delete();
        if ($trash) {
            return response()->json([
                'status' => true,
                'message' => "Your Record has been Permanent Delete!",
                'getDataUrl' => route('admin.overtime.getData'),
            ]);
        }
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Overtime",
            'message' => "Overtime deleted by " . $user->name,
        ]);
        return response()->json([
            'status' => false,
            'message' => "Something went wrong please try later!",
            'getDataUrl' => route('admin.overtime.getData'),
        ]);
    }

    public function massDelete(Request $request)
    {

        $trash = Overtime::whereIn('id', $request->ids)
            ->delete();

        if ($trash) {
            return response()->json([
                'status' => true,
                'message' => "Your Record has been Permanent Delete!",
                'getDataUrl' => route('admin.overtime.getData'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => "Something went wrong please try later!",
            'getDataUrl' => route('admin.overtime.getData'),
        ]);
    }
}
