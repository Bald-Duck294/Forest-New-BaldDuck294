<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\ActivityLog;

class ScheduleController extends Controller
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
    // public function show(Schedule $schedule)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Schedule $schedule)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Schedule $schedule)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Schedule $schedule)
    // {
    //     //
    // }
    private $folder = "admin.schedule.";

    public function index()
    {
        $get_data = route('admin.schedule.getData');
        return View('admin.schedule.index',[
            'get_data' => $get_data,
        ]);
    }
    
    public function getData()
    {
        $schedules = Schedule::get();
        return View('admin.schedule.content',[
            'schedules'=>$schedules,
            'add_new' => route('schedule.create'),
            'count' => Schedule::count(),
            'moveToTrashAllLink' => route('admin.schedule.massDelete'),
        ]);
    }

    public function create()
    {
        return View($this->folder."create",[
            'form_store' => route('schedule.store'),
            ]);
    }

    public function store(ScheduleRequest $request)
    {
        
        $schedule = Schedule::create($request->all());

        return response()->json([
            'status'=>true,
            'message'=>'"New schedule successfully created."',
            'redirect_to' => route('schedule.index')
            ]);
    }

    public function show($id)
    {
        abort(404);
    }

    public function edit($id)
    {
        $schedule = Schedule::where('id',$id)->first();
    	return View('admin.schedule.edit',[
    		'schedule' => $schedule,
    		'form_update' => route('schedule.update',['schedule'=>$schedule]),
    	]);
    }

    public function update(ScheduleRequest $request, $id)
    {
        $schedule = Schedule::where('id',$id)->first();
        $schedule->update($request->all());
        return response()->json([
            'status'=>true,
            'message'=>'"Schedule updated successfully."',
            'redirect_to' => route('schedule.index')
            ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::where('id',$id)->first();
        $schedule->delete();
        return response()->json([
                'status' => true,
                'message' => '"Your record has been deleted!"',
                'getDataUrl' => route('admin.schedule.getData'),
            ]);
    }

    public function massDelete(Request $request)
    {
        $schedules = Schedule::whereIn('id',$request->ids)
                        ->delete();

        return response()->json([
                'status' => true,
                'message' => ' "Your all record has been deleted!" ',
                'getDataUrl' => route('admin.schedule.getData'),
            ]);
    }


}
