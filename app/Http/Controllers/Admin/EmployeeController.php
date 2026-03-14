<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\BankInfo;
use App\Models\Position;
use App\Models\Schedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EmployeeRequest;
use App\ActivityLog;
use DataTables;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     private $folder = "admin.employee.";

     public function index()
     {
         return View('admin.employee.index',[
             'get_data' => route('admin.employee.getData'),
         ]);
     }
 
     public function getData(){
         return View('admin.employee.content',[
             'add_new' => route('employee.create'),
             'getDataTable' => route('admin.employee.getDataTable'),
             'moveToTrashAllLink' => route('admin.employee.massDelete'),
             'employees' => Employee::get(),
         ]);
     }
 
     //not use now : 03-05-2021 @auther : kdvamja
     public function getDataTable(){
         $employees = Employee::get();
         return FacadesDataTables::of($employees)
                     ->addIndexColumn()
                     ->addColumn('avatar', function($data){
                         $avatar = "<img src='".$data->mediaUrl['thumb']."' class='table-user-thumb'>";
                         return $avatar;
                     })
                     ->addColumn('is_active', function($data){
                         if($data->is_active == '1'){
                             $status = "<span class='success-dot' title='Published' title='Active Employee'></span>";
                         }else{
                             $status = "<i class='ik ik-alert-circle text-danger alert-status' title='In-Active Employee'></i>";
                         }
                         return $status;
                     })
                     ->addColumn('details', function($data){
                         $details = "<div class=''>
                                 <b>Gender :</b> <span>".$data->gender."</span></br>
                                 <b>Employee Id :</b> <span>".$data->employee_id."</span></br>
                                 <b>Schedule :</b> <span>".$data->schedule->time_in.'-'.$data->schedule->time_out."</span></br>
                                 <b>Address :</b> <span>".$data->address."</span></br>
                                 </div>";
                         return $details;
                     })
                     ->addColumn('position', function($data){
                         return $data->position->title;
                     })
                     ->addColumn('action', function($data){
                             $btn = "<div class='table-actions'>
                             <a data-href='".route('employee.show',['employee_id'=>$data->employee_id])."' class='show-employee cursure-pointer'><i class='ik ik-eye text-primary'></i></a>
                             <a href='".route("employee.edit",['employee_id'=>$data->employee_id])."'><i class='ik ik-edit-2 text-dark'></i></a>
                             <a data-href='".route("employee.destroy",['id'=>$data->id])."' class='delete cursure-pointer'><i class='ik ik-trash-2 text-danger'></i></a>
                             </div>";
                             return $btn;
                     })
                     ->rawColumns(['action','avatar','is_active','position','details'])
                     ->toJson();
     }
 
     public function create()
     {   
         $schedules = Schedule::get();
         $positions = Position::get();
         
         return View("admin.employee.create",[
            // 'next_form' =>route('employee.nextForm'),
            'form_store' => route('employee.store'),
             'schedules' => $schedules,
             'positions' => $positions,
            
         ]);
     }

    //  public function nextForm(EmployeeRequest $request){
    // // dd($request);
       
    //     return View("admin.employee.bankDetail",[
    //          'employee'=> $request,
    //          'form_store' => route('employee.store'),    
    //      ]);
    //  }
     
     public function store(EmployeeRequest $request)
     {  
        $user= session('user');
        // dd($request);
         $data = [
             'first_name' => $request->first_name,
             'last_name' => $request->last_name,
             'phone' => $request->phone,
             'email' => $request->email,
             'birthdate' => $request->birthdate,
             'gender' => $request->gender,
             'schedule_id' => $request->schedule_id,
             'position_id' => $request->position_id,
             'address' => $request->address,
             'remark' => $request->remark,
             'rate_per_hour' => $request->rate_per_hour,
             'salary' => $request->salary,
             'is_active' => $request->is_active,
         ];


         $employee = Employee::create($data);

         $bank_detail = [

            'user_id' => $employee->id,
            'bank_name' => $request->bank_name,
            'bank_account_no' => $request->bank_account_no,
            'ifsc_code' => $request->ifsc_code,
            'pan_no' => $request->pan_no,
           
        ];
        $bank = BankInfo::create($bank_detail);
         //below here i am save the image which is given by user and save that id to our parent table as a foreign key
         if($request->has('media') && file_exists(storage_path('media/uploads/'.$request->input('media')))){
             $media = $employee->addMedia(storage_path('media/uploads/' . $request->input('media')))->toMediaCollection('avatar');
             $employee->media_id = $media->id;
             $employee->save(); //save media_id here
         }
 
         //Mail::to($employee->email)->send(new StaffCreated($data));

         ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Employee",
            'message' => "Employee created by " . $user->name,
        ]);
         return response()->json([
             'status'=>true,
             'message'=>'New Employee Created Successfully.',
             'redirect_to' => route('employee.index')
             ]);
     }
 
     public function show(Employee $employee){   
         return View('admin.employee.show',[
             'employee'=>$employee,
         ]);
     }
 
     public function edit(Employee $employee)
     {

        // dd($employee);
        $schedules = Schedule::get();
        $positions = Position::get();
        $bankInfo = BankInfo::where('user_id',$employee->id)->first();
        // dd($bankInfo);
         return View('admin.employee.edit',[
             'employee' => $employee,
             'bankInfo'=> $bankInfo,
             'form_update' => route('employee.update',$employee),
             'schedules' => $schedules,
             'positions' => $positions,
             'removeAvatar' => route('removeMedia',['model'=>'Employee','model_id'=>$employee->id,'collection'=>'avatar']),
         ]);
     }
 
     public function update(EmployeeRequest $request, Employee $employee)
     {
    //  dd($request);
    $user = session('user');
         $data = [

             'first_name' => $request->first_name,
             'last_name' => $request->last_name,
             'phone' => $request->phone,
             'email' => $request->email,
             'birthdate' => $request->birthdate,
             'gender' => $request->gender,
             'schedule_id' => $request->schedule_id,
             'position_id' => $request->position_id,
             'address' => $request->address,
             'remark' => $request->remark,
             'rate_per_hour' => $request->rate_per_hour,
             'salary' => $request->salary,
             'is_active' => $request->is_active,

         ];
         $employee->update($data);
        //dd($employee);
          $bank = BankInfo::where('user_id',$employee->id)->first();
        //dd($bank);
         $bank_detail = [

            'user_id' => $employee->id,
            'bank_name' => $request->bank_name,
            'bank_account_no' => $request->bank_account_no,
            'ifsc_code' => $request->ifsc_code,
            'pan_no' => $request->pan_no,
           
        ];

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Employee",
            'message' => "Employee updated by " . $user->name,
        ]);

       $bank->update($bank_detail);
 
         if($request->has('media') && file_exists(storage_path('media/uploads/'.$request->input('media')))){
             $media = $employee->addMedia(storage_path('media/uploads/' . $request->input('media')))->toMediaCollection('avatar');
             $employee->media_id = $media->id;
             $employee->save(); // save media_id here
         }
 
         return response()->json([
             'status'=>true,
             'message'=> $employee->employee_id.' updated successfully.',
             'redirect_to' => route('employee.index')
             ]);
     }
 
     protected function permanentDelete($id){
         $trash = Employee::find($id);
         if (count($trash->getMedia('avatar')) > 0) {
             foreach ($trash->getMedia('avatar') as $media) {
                 $media->delete();
             }
         }
         $trash->delete();
         return true;
     }
 
     protected function massPermanentDelete($ids){
         $employees = Employee::whereIn('id',$ids)
                         ->get();
         foreach ($employees as $employee) {
             $this->permanentDelete($employee->id);
         }
         return true;
     }
 
     public function destroy(Request $request,$id)
     {   
        $user= session('user');
         $trash = $this->permanentDelete($id);
      
         if($trash){
             return response()->json([
                 'status' => true,
                 'message' => "Your Record has been Permanent Delete!",
                 'getDataUrl' => route('admin.employee.getData'),
             ]);

             ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "delete Employee",
                'message' => "Employee deleted by " . $user->name,
            ]);
         }

         return response()->json([
             'status' => false,
             'message' => "Something went wrong please try later!",
             'getDataUrl' => route('admin.employee.getData'),
         ]);
     }
 
     public function massDelete(Request $request){
         //this is for permanent delete all record
         $trash = $this->massPermanentDelete($request->ids);
 
         if($trash){
             return response()->json([
                 'status' => true,
                 'message' => "Your Record has been Permanent Delete!",
                 'getDataUrl' => route('admin.employee.getData'),
             ]);
         }
         return response()->json([
             'status' => false,
             'message' => "Something went wrong please try later!",
             'getDataUrl' => route('admin.employee.getData'),
         ]);
     }

     public function offerEmployee(Request $request){
        // dd($request->emp_data);
            $offerEmployee= json_decode($request->emp_data,'true');
            $schedules = Schedule::get();
            $positions = Position::get();
   
            // dd($offerEmployee);

            return View("admin.employee.create",[
               // 'next_form' =>route('employee.nextForm'),
               'form_store' => route('employee.store'),
                'schedules' => $schedules,
                'positions' => $positions,
                'offerEmployee'=> $offerEmployee
            ]);
     }
}
