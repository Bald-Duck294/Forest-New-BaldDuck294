<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Position;
use Illuminate\Http\Request;
use App\ActivityLog;

use App\Http\Requests\PositionRequest;

class PositionController extends Controller
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
    // public function show(Position $position)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Position $position)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Position $position)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Position $position)
    // {
    //     //
    // }


    private $folder = "admin.position.";

    public function index()
    {
        $get_data = route('admin.position.getData');
        return View('admin.position.index',[
            'get_data' => $get_data
        ]);
    }
    
    public function getData()
    {
        $positions = Position::get();
        return View('admin.position.content',[
            'positions'=>$positions,
            'add_new' => route('position.create'),
            'moveToTrashAllLink' => route('admin.position.massDelete'),
        ]);
    }

    public function create()
    {
        return View($this->folder."create",[
            'form_store' => route('position.store'),
            ]);
    }

    public function store(PositionRequest $request)
    {
        $position = Position::create($request->all());

        return response()->json([
            'status'=>true,
            'message'=>'New Position created successfully.',
            'redirect_to' => route('position.index')
            ]);
    }

    public function show(Position $position)
    {
        abort(404);
    }

    public function edit(Position $position)
    {
    	return View('admin.position.edit',[
    		'position' => $position,
    		'form_update' => route('position.update',['position'=>$position]),
    	]);
    }

    public function update(PositionRequest $request, Position $position)
    {
        $position->update($request->all());
        return response()->json([
            'status'=>true,
            'message'=>'Position '.$position->title.'successfully updated.',
            'redirect_to' => route('position.index')
            ]);
    }

    public function destroy(Position $position)
    {
        $position->delete();
        return response()->json([
                'status' => true,
                'message' => '"Your record has been deleted!"',
                'getDataUrl' => route('admin.position.getData'),
            ]);
    }

    public function massDelete(Request $request)
    {
        $positions = Position::whereIn('id',$request->ids)
                        ->delete();

        return response()->json([
                'status' => true,
                'message' => '"Your all record has been deleted!"',
                'getDataUrl' => route('admin.position.getData'),
            ]);
    }
}
