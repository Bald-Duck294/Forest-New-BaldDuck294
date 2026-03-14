<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Deduction;
use App\Models\Position;
use App\Models\Employee;
use App\Models\Admins;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $deductions = Deduction::all();
        // dd($deductions);
        $total_attendance = Attendance::count();
    	$ontime_attendance = Attendance::where("ontime_status",1)->count();
    	$percentage =  $total_attendance == 0 ? 0 : number_format(($ontime_attendance/$total_attendance)*100,2);
       
                $admins = Admins::count() - 1;
                $count_positions = Position::count();
                $count_deductions = Deduction::count();
                $schedules = Schedule::count();
            
                $on_time_attendance = Attendance::where(["date"=>date("Y-m-d",time()),"ontime_status"=>1])->count();
                $late_attendance = Attendance::where(["date"=>date("Y-m-d",time()),"ontime_status"=>0])->count();


        $employees = Employee::count();
        $deductions = Deduction::latest('id')->get();
        $total_deduction = Deduction::sum('amount');
        $positions = Position::inRandomOrder()->get();
        return View("admin.dashboard",compact('deductions','total_deduction','positions','employees','percentage','count_positions',
        'count_deductions','on_time_attendance','late_attendance','admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}
