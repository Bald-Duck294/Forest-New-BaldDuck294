<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GlobalSuperAdminController extends Controller
{
    public function dashboard()
    {
        // KPI CARDS
        $totalCompanies = DB::table('company_details')->count();
        $totalSuperadmins = DB::table('users')->where('role_id', 1)->count();
        $totalAdmins = DB::table('users')->where('role_id', 7)->count();
        $activeUsers = DB::table('users')->where('isActive', 1)->count();

        // Just a preview of the latest 5 companies for the dashboard
        $recentCompanies = DB::table('company_details')
            ->leftJoin('users', function ($join) {
                $join->on('company_details.id', '=', 'users.company_id')
                    ->where('users.role_id', 7);
            })
            ->select(
                'company_details.id',
                'company_details.name',
                'company_details.isActive',
                DB::raw('COUNT(users.id) as admin_count')
            )
            ->groupBy('company_details.id', 'company_details.name', 'company_details.isActive')
            ->latest('company_details.id')
            ->limit(5)
            ->get();

        return view('global.dashboard', compact(
            'totalCompanies',
            'totalSuperadmins',
            'totalAdmins',
            'activeUsers',
            'recentCompanies'
        ));
    }

    public function companies(Request $request)
    {
        $query = DB::table('company_details')
            ->leftJoin('users', function ($join) {
                $join->on('company_details.id', '=', 'users.company_id')
                    ->where('users.role_id', 7);
            })
            ->select(
                'company_details.id',
                'company_details.name',
                'company_details.type',
                'company_details.isActive',
                DB::raw('COUNT(users.id) as admin_count')
            )
            ->groupBy(
                'company_details.id',
                'company_details.name',
                'company_details.type',
                'company_details.isActive'
            );

        // Apply Search Filter
        if ($request->filled('search')) {
            $query->where('company_details.name', 'like', '%' . $request->search . '%');
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $status = $request->status == 'active' ? 1 : 0;
            $query->where('company_details.isActive', $status);
        }

        $companies = $query->latest('company_details.id')->paginate(10)->withQueryString();

        return view('global.companies', compact('companies'));
    }

    public function createCompany()
    {
        return view('global.add_company');
    }


    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required',
            'email' => 'nullable|email',
            'address' => 'nullable|string'
        ]);

        DB::table('company_details')->insert([
            'name' => $request->name,
            'contact' => $request->contact,
            'email' => $request->email,
            'contact_person' => $request->contact_person,
            'contact_person_contact' => $request->contact_person_contact,
            'contact_person_designation' => $request->contact_person_designation,
            'address' => $request->address,
            'empLimit' => $request->empLimit,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'isActive' => $request->isActive
        ]);

        return redirect()->route('global.dashboard')
            ->with('success', 'Company created successfully');
    }



    public function editCompany($id)
    {
        $company = DB::table('company_details')->where('id', $id)->first();

        return view('global.edit_company', compact('company'));
    }



    public function updateCompany(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required',
            'email' => 'nullable|email'
        ]);


        DB::table('company_details')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'contact' => $request->contact,
                'email' => $request->email,
                'contact_person' => $request->contact_person,
                'contact_person_contact' => $request->contact_person_contact,
                'contact_person_designation' => $request->contact_person_designation,
                'address' => $request->address,
                'empLimit' => $request->empLimit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'type' => $request->type,
                'isActive' => $request->isActive
            ]);


        return redirect()->route('global.dashboard')
            ->with('success', 'Company updated successfully');
    }

    public function superAdmins(Request $request)
    {
        $query = DB::table('users')
            ->where('role_id', 1)
            ->whereNull('deleted_at');

        // ✅ STATUS FILTER
        if ($request->status === 'active') {
            $query->where('isActive', 1);
        } elseif ($request->status === 'inactive') {
            $query->where('isActive', 0);
        }

        // ✅ SEARCH (THIS IS NEW)
        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('contact', 'LIKE', "%{$search}%")
                    ->orWhere('company_name', 'LIKE', "%{$search}%");
            });
        }

        $superadmins = $query
            ->select(
                'id',
                'name',
                'username',
                'email',
                'contact',
                'company_name',
                'isActive'
            )
            ->latest()
            ->paginate(5)
            ->withQueryString(); // 🔥 keeps search + filter

        return view('global.superadmins', compact('superadmins'));
    }

    public function viewSuperAdmin($id)
    {
        $superadmin = DB::table('users')
            ->where('id', $id)
            ->where('role_id', 1) // enforce it's actually superadmin
            ->first();

        if (!$superadmin) {
            return redirect()->back()->with('error', 'Superadmin not found');
        }

        return view('global.superadmin_view', compact('superadmin'));
    }

    public function editUser($id)
    {
        $editUser = \App\Models\User::findOrFail($id);
        $roles = \App\Models\Role::all(); // Or however you fetch roles

        // This points specifically to your new file: global/edit_user.blade.php
        return view('global.edit_user', compact('editUser', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        // 1. Validate the basic fields
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed', // 'nullable' is key here
        ]);

        // 2. Prepare data for update (excluding password for now)
        $data = $request->except(['password', 'password_confirmation']);

        // 3. Only add password to the update array if it's NOT empty
        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        // 4. Perform the update
        $user->update($data);

        return redirect()->route('global.superadmins.view', $id)
            ->with('success', 'User updated successfully');
    }


    public function admins(Request $request)
    {
        $query = \App\Models\User::where('role_id', 7)
            ->select('id', 'name', 'username', 'email', 'contact', 'company_name', 'isActive', 'created_at');

        // 1. Search Filter (Name, Email, or Username)
        $query->when($request->search, function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        });

        // 2. Status Filter (Active/Inactive)
        $query->when($request->status, function ($q) use ($request) {
            if ($request->status == 'active') {
                $q->where('isActive', 1);
            } elseif ($request->status == 'inactive') {
                $q->where('isActive', 0);
            }
        });

        // 3. Pagination (10 per page)
        $admins = $query->latest()->paginate(10)->withQueryString();

        return view('global.admins', compact('admins'));
    }

    public function viewAdmin($id)
    {
        $admin = DB::table('users')
            ->where('id', $id)
            ->where('role_id', 7)
            ->first();

        if (!$admin) {
            return redirect()->back()->with('error', 'Admin not found');
        }

        return view('global.admin_view', compact('admin'));
    }

    public function viewCompanyDashboard($id)
    {
        // Set the session so the sidebar knows we are "inside" this company
        session(['simulated_company_id' => $id]);

        // Redirect to the actual company dashboard (usually /home or /dashboard)
        return redirect('/home');
    }

    public function exitCompanyDashboard()
    {
        // Clear the session to return to Global view
        session()->forget(['simulated_company_id']);
        return redirect()->route('global.dashboard');
    }
}
