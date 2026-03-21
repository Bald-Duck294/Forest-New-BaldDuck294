<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class ModuleController extends Controller
{

    private $modules = [
        "attendance",
        "incidence",
        "track",
        "offline",
        "SOS",
        "allReport",
        "autoAttendance",
        "patrol-list",
        "know-your-beat"
    ];

    public function index(Request $request)
    {
        $companies = Company::all();

        $selectedCompany = null;

        if ($request->company_id) {
            $selectedCompany = Company::find($request->company_id);
        }

        return view('modules.index', [
            'companies' => $companies,
            'modules' => $this->modules,
            'selectedCompany' => $selectedCompany
        ]);
    }


    public function update(Request $request)
    {
        $company = Company::findOrFail($request->company_id);

        $features = [];

        foreach ($this->modules as $module) {
            $features[$module] = $request->has($module);
        }

        $company->update([
            'features' => $features
        ]);

        return back()->with('success', 'Modules updated');
    }
}
