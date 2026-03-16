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

        $totalSuperadmins = DB::table('users')
            ->where('role_id', 1)
            ->count();

        $totalAdmins = DB::table('users')
            ->where('role_id', 7)
            ->count();

        $activeUsers = DB::table('users')
            ->where('isActive', 1)
            ->count();


        // COMPANY LIST WITH ADMIN COUNT
        $companies = DB::table('company_details')
            ->leftJoin('users', function ($join) {
                $join->on('company_details.id', '=', 'users.company_id')
                    ->where('users.role_id', 2);
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
            )
            ->get();


        return view('global.dashboard', compact(
            'totalCompanies',
            'totalSuperadmins',
            'totalAdmins',
            'activeUsers',
            'companies'
        ));
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
}
