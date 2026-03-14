<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class AttendanceController extends Controller
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
    // public function show(Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Attendance $attendance)
    // {
    //     //
    // }


    private $folder = "admin.attendance.";

    public function index()
    {
        return View('admin.attendance.index', [
            'get_data' => route('admin.attendance.getData'),
        ]);
    }

    public function getData()
    {
        return View('admin.attendance.content', [
            'add_new' => route('attendance.create'),
            'getDataTable' => route('admin.attendance.getDataTable'),
            'moveToTrashAllLink' => route('admin.attendance.massDelete'),
        ]);
    }

    public function getDataTable()
    {
        $attendance = Attendance::latest();

        // dd($attendance);
        // return view('admin.attendance.content',compact($attendance));
        return FacadesDataTables::of($attendance)

            ->addIndexColumn()
            ->addColumn('employee', function ($data) {

                return "<div class='row'><div class='col-md-3 text-center'><img src='" . $data->employee->media_url['thumb'] . "' class='rounded-circle table-user-thumb'></div><div class='col-md-6 col-lg-6 my-auto'><b class='mb-0'>" . $data->employee->first_name . " " . $data->employee->last_name . "</b><p class='mb-2' title='" . $data->employee->employee_id . "'><small><i class='ik ik-at-sign'></i>" . $data->employee->employee_id . "</small></p></div><div class='col-md-4 col-lg-4'><small class='text-muted float-right'></small></div></div>";
            })
            ->addColumn('action', function ($data) {
                $btn = "<div class='table-actions'>
                            <a href='" . route("attendance.edit", [$data->id]) . "'><i class='ik ik-edit-2 text-dark'></i></a>
                            <a data-href='" . route("attendance.destroy", [$data->id]) . "' class='delete cursure-pointer'><i class='ik ik-trash-2 text-danger'></i></a>
                            </div>";
                return $btn;
            })
            ->addColumn('time_in_details', function ($data) {
                $status = "<div>";
                $status .= "<span class='float-left'>";
                $status .= $data->time_in;
                $status .= "</span>";
                $status .= "<span class='float-right'>";
                if (!$data->ontime_status) {
                    $status .= "<span class='text-danger'>LATE</span>";
                } else {
                    $status .= "<span class='text-primary'>ONTIME</span>";
                }
                $status .= "</span>";
                return $status;
            })
            ->addColumn('work_hr', function ($data) {

                return $data->num_hour . "/hr";
            })
            ->rawColumns(['employee', 'action', 'time_in_details', 'work_hr'])
            ->toJson();
    }

    public function create()
    {
        $employees = Employee::get();
        return View($this->folder . "create", [
            'form_store' => route('attendance.store'),
            'employees' => $employees,
        ]);
    }

    public function store(AttendanceRequest $request)
    {



        $data = [
            'date' => $request->date,
            'employee_id' => $request->employee_id,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'num_hour' => 0,
        ];
        $attendance = Attendance::create($data);
        $attendance->save();
        return response()->json([
            'status' => true,
            'message' => ' "New attendance successfully added." ',
            'redirect_to' => route('attendance.index')
        ]);
    }

    public function show(Attendance $attendance)
    {
        abort(404);
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::get();
        return View('admin.attendance.edit', [
            'attendance' => $attendance,
            'form_update' => route('attendance.update', ['attendance' => $attendance]),
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {


        // $num_hour = $request->time_in->diffAsCarbonInterval($request->time_out)->hours;
        // dd($num_hour);
        $data = [
            'date' => $request->date,
            'employee_id' => $request->employee_id,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'num_hour' => 0,

        ];
        $attendance->update($data);

        return response()->json([
            'status' => true,
            'message' => ' "Attendance successfully updated." ',
            'redirect_to' => route('attendance.index')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $trash = Attendance::where('id', $id)->delete();
        if ($trash) {
            return response()->json([
                'status' => true,
                'message' => ' "Your record has been permanently delete!" ',
                'getDataUrl' => route('admin.attendance.getData'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => '"Something went wrong,please try again later."',
            'getDataUrl' => route('admin.attendance.getData'),
        ]);
    }

    public function massDelete(Request $request)
    {

        $trash = Attendance::whereIn('id', $request->ids)
            ->delete();

        if ($trash) {
            return response()->json([
                'status' => true,
                'message' => '"Your record has been permanently delete!"',
                'getDataUrl' => route('admin.attendance.getData'),
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => '"Something went wrong,please try again later."',
            'getDataUrl' => route('admin.attendance.getData'),
        ]);
    }
}
