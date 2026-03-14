<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollComponentRequest;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use App\ActivityLog;

class PayComponentController extends Controller
{
    private $folder = "admin.paycomponent.";
    public function index()
    {
        $get_data = route('admin.paycomponent.getData');
        
        return View('admin.paycomponent.index', [
            'get_data' => $get_data,
        ]);

    }

    public function getData()
    {
        
        $paycomponents = PayrollComponent::get();
        return View('admin.paycomponent.content', [
            'paycomponents' => $paycomponents,
            'add_new' => route('paycomponent.create'),
            'sum' => PayrollComponent::sum('amount'),
            'moveToTrashAllLink' => route('admin.paycomponent.massDelete'),
        ]);
    }

    public function create()
    {
        //dd('a');
        $user =session('user');
        $paycomponents = PayrollComponent::get();

      

        return View($this->folder . "create", [
            'add_new' => route('paycomponent.create'),
            'form_store' => route('paycomponent.store'),
            'paycomponents' => $paycomponents,
            'sum' => PayrollComponent::sum('amount'),
            'moveToTrashAllLink' => route('admin.paycomponent.massDelete'),
        ]);
    }

    public function store(PayrollComponentRequest $request)
    {
        $user = session('user');
        $data= $request->all();
        $data['company_id'] = $user->company_id;
        $paycomponent = PayrollComponent::create($data);

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Pay Component",
            'message' => "Pay component created by " . $user->name,
        ]);
        
        return response()->json([
            'status' => true,
            'message' => ' "New Pay Component successfully created." ',
            'redirect_to' => route('paycomponent.index')
        ]);
    }

    public function show(PayrollComponent $paycomponent)
    {
        abort(404);
    }

    public function edit($id)
    {
        $paycomponent= PayrollComponent::find($id);
        // dd();
        return View('admin.paycomponent.edit', [
            'paycomponent' => $paycomponent,
            'form_update' => route('paycomponent.update',['paycomponent' => (object) ['id' => $paycomponent->id]] ),
        ]);
    }

    public function update(Request $request)
    {
        // dd($request);
        $user= session('user');
        // $deduction = Deduction::find($request->deduction['id']);
        $paycomponent = PayrollComponent::find($request->id);
        $paycomponent->update($request->all());
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Pay Component",
            'message' => "Pay component updated by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' =>'"'. $paycomponent->name . 'successfully updated."',
            'redirect_to' => route('paycomponent.index')
        ]);
    }

    public function destroy($id)
    {
        //dd($paycomponent)
        $user=session('user');
        $paycomponent= PayrollComponent::find($id);
        $paycomponent->delete();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Pay Component",
            'message' => "Pay component deleted by " . $user->name,
        ]);
        return response()->json([
            'status' => true,
            'message' => ' "Your record has been deleted!" ',
            'getDataUrl' => route('admin.paycomponent.getData'),
        ]);
    }

    public function massDelete(Request $request)
    {
        $paycomponents = PayrollComponent::whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => ' "Your all record has been deleted!" ',
            'getDataUrl' => route('admin.paycomponent.getData'),
        ]);
    }
}
