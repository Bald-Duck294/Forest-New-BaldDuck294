<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
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
    // public function show(Admins $admins)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Admins $admins)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Admins $admins)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Admins $admin)
    // {
    //     //
    // }


    private $module = "admin.profile.";

    public function index()
    {
        $admin = Auth()->user();
        return View('admin.profile.profile', [
            'user' => $admin,
            'form_url' => route("admin.profile.update")
        ]);
    }

    public function update(Request $request)
    {

        //  dd($request);
        // dd(auth()->id());
        $userpassword = DB::table("users")->where("id", $request->id)->first()->password;

        // dd($request->password,$userpassword);

        if (isset($request->password) || Hash::check($request->password, $userpassword)) {

            $password = Hash::make($request->password);
            if (isset($request->new_password)) {
                $password = Hash::make($request->new_password);
            }
            $data = [
                'username' => $request->username,
                'email' => $request->email,
                'password' => $password,
            ];
            User::find($request->id)->update($data);
            return Redirect()->back()
                ->with('bgcolor', 'bg-success')
                ->withErrors(['errors' => '"Profile successfully updated."']);
        } else {


            return Redirect()->back()
                ->with('bgcolor', 'bg-danger')
                ->withErrors(['errors' => '"Please enter your password or password does not Match."']);
        }
    }
}
