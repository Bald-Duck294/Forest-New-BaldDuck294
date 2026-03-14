<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\CashAdvance;
use App\Users;
use Illuminate\Http\Request;
use App\SiteAssign;
use App\ActivityLog;
use App\Http\Requests\CashAdvanceRequest;

class CashAdvanceController extends Controller
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
    // public function show(CashAdvance $cashAdvance)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(CashAdvance $cashAdvance)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, CashAdvance $cashAdvance)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(CashAdvance $cashAdvance)
    // {
    //     //
    // }

    private $folder = "admin.cashadvance.";

    public function index()
    {
        return View('admin.cashadvance.index', [
            'get_data' => route('admin.cashadvance.getData'),
        ]);
    }

    public function getData()
    {
        $user = session('user');
        if ($user->role_id == 2) {
            $site_assign = SiteAssign::where('user_id', $user->id)->first();
            $cashadvance = [];
            if ($site_assign) {
                $siteArray = json_decode($site_assign->site_id, true);
                $site_users = SiteAssign::whereIn('site_id', $siteArray)->pluck('user_id')->toArray();
                $cashadvance = CashAdvance::whereIn('user_id', $site_users)->where('company_id', $user->company_id)->with('user')->get();
            }
        } else
            $cashadvance = CashAdvance::where('company_id', $user->company_id)->with('user')->get();
        return View('admin.cashadvance.content', [
            'add_new' => route('cashadvance.create'),
            //'getDataTable' => route('admin.cashadvance.getDataTable'),
            'moveToTrashAllLink' => route('admin.cashadvance.massDelete'),
            'cashadvances' => $cashadvance,
        ]);
    }

    public function getDataTable()
    {
        $cashadvance = CashAdvance::get();

        return View('admin.cashadvance.content', compact('cashadvance'));
        //return Datatables::of($cashadvance)
        //             ->addIndexColumn()
        //             ->addColumn('employee', function($data){
        //             	return "<div class='row'><div class='col-md-3 text-center'><img src='".$data->employee->media_url['thumb']."' class='rounded-circle table-user-thumb'></div><div class='col-md-6 col-lg-6 my-auto'><b class='mb-0'>".$data->employee->first_name." ".$data->employee->last_name."</b><p class='mb-2' title='".$data->employee->employee_id."'><small><i class='ik ik-at-sign'></i>".$data->employee->employee_id."</small></p></div><div class='col-md-4 col-lg-4'><small class='text-muted float-right'></small></div></div>";
        //             })
        //             ->addColumn('action', function($data){
        //                     $btn = "<div class='table-actions'>
        //                     <a href='".route("admin.cashadvance.edit",['slug'=>$data->slug])."'><i class='ik ik-edit-2 text-dark'></i></a>
        //                     <a data-href='".route("admin.cashadvance.destroy",['slug'=>$data->slug])."' class='delete cursure-pointer'><i class='ik ik-trash-2 text-danger'></i></a>
        //                     </div>";
        //                     return $btn;
        //             })
        //             ->rawColumns(['employee','action'])
        //->toJson();
    }

    public function create()
    {
        $employees = Users::get();
        return View("admin.cashadvance.create", [
            'form_store' => route('cashadvance.store'),
            'employees' => $employees,
        ]);
    }

    public function store(CashAdvanceRequest $request)
    {
        $user = session('user');
        $data = [
            'title' => $request->title,
            'rate_amount' => $request->rate_amount,
            'date' => $request->date,
            'user_id' => $request->employee_id,
            'company_id' => $user->company_id
        ];
        $cashadvance = CashAdvance::create($data);
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Cash Advance",
            'message' => "Cash advance created by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => ' "New cash-advance successfully added." ',
            'redirect_to' => route('cashadvance.index')
        ]);
    }

    public function show(CashAdvance $cashadvance)
    {
        abort(404);
    }

    public function edit(CashAdvance $cashadvance)
    {
        // dd($cashadvance);
        $employees = Users::get();
        return View('admin.cashadvance.edit', [
            'cashadvance' => $cashadvance,
            'form_update' => route('cashadvance.update', ['cashadvance' => $cashadvance]),
            'employees' => $employees,
        ]);
    }

    public function update(CashAdvanceRequest $request, CashAdvance $cashadvance)
    {
        $user = session('user');
        $data = [
            'title' => $request->title,
            'rate_amount' => $request->rate_amount,
            'date' => $request->date,
            'user_id' => $request->employee_id,
        ];
        $cashadvance->update($data);

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Cash Advance",
            'message' => "Cash advance updated by " . $user->name,
        ]);

        return response()->json([
            'status' => true,
            'message' => $cashadvance->title . ' updated.',
            'redirect_to' => route('cashadvance.index')
        ]);
    }

    public function destroy(CashAdvance $cashadvance)
    {
        $user= session('user');
        $trash = $cashadvance->delete();
        if ($trash) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Delete Cash Advance",
                'message' => "Cash advance deleted by " . $user->name,
            ]);
            return response()->json([
                'status' => true,
                'message' => ' "Your record has been permanently deleted!" ',
                'getDataUrl' => route('admin.cashadvance.getData'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => ' "Something went wrong,please try again later." ',
            'getDataUrl' => route('admin.cashadvance.getData'),
        ]);
    }

    public function massDelete(Request $request)
    {
       $user = session('user');
        $trash = CashAdvance::whereIn('id', $request->ids)
            ->delete();

        if ($trash) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Multiple Record Delete In Cash Advance",
                'message' => "Cash advance deleted by " . $user->name,
            ]);
            return response()->json([
                'status' => true,
                'message' => ' "Your record has been permanently deleted!" ',
                'getDataUrl' => route('admin.cashadvance.getData'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => ' "Something went wrong,please try again later!" ' ,
            'getDataUrl' => route('admin.cashadvance.getData'),
        ]);
    }
}
