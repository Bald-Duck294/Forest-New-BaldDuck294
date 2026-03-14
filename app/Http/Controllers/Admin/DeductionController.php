<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeductionRequest;
use App\ActivityLog;
use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
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
    // public function show(Deduction $deduction)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Deduction $deduction)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Deduction $deduction)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Deduction $deduction)
    // {
    //     //
    // }



    private $folder = "admin.deduction.";



    public function index()
    {
        // dd('index');
        $user = session('user');
        $get_data = route('admin.deduction.getData');
        return View('admin.deduction.index', [
            'get_data' => $get_data,
        ]);
    }

    public function getData()
    {
        $user = session('user');
        $deductions = Deduction::get();
        return View('admin.deduction.content', [
            'deductions' => $deductions,
            'add_new' => route('deduction.create'),
            'sum' => Deduction::sum('amount'),
            'moveToTrashAllLink' => route('admin.deduction.massDelete'),
        ]);
    }

    public function create()
    {
        return View($this->folder . "create", [
            'form_store' => route('deduction.store'),
        ]);
    }

    public function store(DeductionRequest $request)
    {
        $user = session('user');

        $data = $request->all();
        $data['company_id'] = $user->company_id;
        
        $deduction = Deduction::create($data);
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Deduction",
            'message' => "Deduction created by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'New deduction created successfully.',
            'redirect_to' => route('deduction.index')
        ]);
    }

    public function show(Deduction $deduction)
    {
        abort(404);
    }

    public function edit($id)
    {
        // dd("edit deduction");
        $deduction = Deduction::find($id);
        // dd($deduction);
        return View('admin.deduction.edit', [
            'deduction' => $deduction,
            'form_update' => route('deduction.update', ['deduction' => (object) ['id' => $deduction->id]]),
        ]);
    }

    public function update(DeductionRequest $request)
    {
        // dd($request->deduction['id']);
        $user = session('user');
        $deduction = Deduction::find($request->deduction['id']);

        $deduction->update($request->all());

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Deduction",
            'message' => "Deduction updated by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => '"'. $request->name . ' successfully updated."',
            'redirect_to' => route('deduction.index')
        ]);
    }

    public function destroy($id)
    {
        // dd($id);
        $user = session('user');
        $deduction = Deduction::find($id);
        $deduction->delete();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Deduction",
            'message' => "Deduction deleted by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => '"Your record has been deleted!"',
            'getDataUrl' => route('admin.deduction.getData'),
        ]);
    }

    public function massDelete(Request $request)
    {
        $user = session('user');
        $deductions = Deduction::whereIn('id', $request->ids)
            ->delete();

            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Multiple Record Delete In Deduction",
                'message' => "Deduction deleted by " . $user->name,
            ]);

        return response()->json([
            'status' => true,
            'message' => '"Your all record has been deleted!"',
            'getDataUrl' => route('admin.deduction.getData'),
        ]);
    }
}
