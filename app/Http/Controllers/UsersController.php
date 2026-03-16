<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use App\RoleIdList;
use App\SiteAssign;
use App\ActivityLog;
use Validator;
use Hash;

class UsersController extends Controller
{
    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = session('user');
        if ($user) {
            $editUser = Users::find($id);
            if (!$editUser) {
                return redirect()->back()->with('error', 'User not found.');
            }
            $roles = RoleIdList::whereIn('id', [1, 2, 3, 7])->orderBy('sequence', 'ASC')->get();
            return view('user_edit')->with('editUser', $editUser)->with('roles', $roles);
        }
        return redirect()->route('login');
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = session('user');
        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'code_name' => 'nullable|max:255',
                'role_id' => 'required|exists:role_id_list,id',
                'email' => 'nullable|email|max:255|unique:users,email,' . $id,
                'contact' => 'required|numeric|digits_between:10,15',
                'dob' => 'nullable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'address' => 'nullable|max:500',
                'password' => 'nullable|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $editUser = Users::find($id);
            if (!$editUser) {
                return redirect()->back()->with('error', 'User not found.');
            }

            $oldRoleId = $editUser->role_id;

            $editUser->name = $request->name;
            $editUser->code_name = $request->code_name;
            $editUser->role_id = $request->role_id;
            $editUser->email = $request->email;
            $editUser->contact = $request->contact;
            $editUser->dob = $request->dob;
            $editUser->gender = $request->gender;
            $editUser->address = $request->address;

            if ($request->filled('password')) {
                $editUser->password = \Hash::make($request->password);
            }

            $editUser->save();

            if ($oldRoleId != $request->role_id) {
                SiteAssign::where('user_id', $id)->delete();
            }

            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Update User Profile",
                'message' => "User " . $editUser->name . " profile and account details updated by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);

            // Determine redirection based on role
            switch ($editUser->role_id) {
                case 1: // Super Admin
                case 7: // Admin
                    return redirect()->route('admin_details', $id)->with('success', 'User profile updated successfully.');
                case 2: // Supervisor
                    return redirect()->route('supervisorDetails', $id)->with('success', 'User profile updated successfully.');
                case 3: // Employee/Guard
                    return redirect()->route('clients.clientguard_read', [0, 0, $id])->with('success', 'User profile updated successfully.');
                default:
                    return redirect()->route('clients.clientguard_read', [0, 0, $id])->with('success', 'User profile updated successfully.');
            }
        }
        return redirect()->route('login');
    }
}
