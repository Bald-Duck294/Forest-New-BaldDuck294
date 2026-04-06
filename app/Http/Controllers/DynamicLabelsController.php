<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FieldMaster;
use App\Models\CompanyFieldLabel;
use App\Services\LabelService;
use DB;

class DynamicLabelsController extends Controller
{
    // main page
    public function index()
    {
        $masters = FieldMaster::all();
        $user = auth()->user();

        // Check if the user is a Global Superadmin (Role ID 8)
        $isGlobalAdmin = $user->role_id == 8;

        if ($isGlobalAdmin) {
            // Global admin sees all companies
            $companies = \App\Models\Company::with('fieldLabels')->get();
        } else {
            // Company admin ONLY gets their own company
            $companies = \App\Models\Company::where('id', $user->company_id)
                ->with('fieldLabels')
                ->get();
        }

        // Pass $isGlobalAdmin to the view
        return view('DynamicLabel.index', compact('masters', 'companies', 'isGlobalAdmin'));
    }

    // create master label
    public function storeMaster(Request $request)
    {
        FieldMaster::create([
            'field_key' => $request->field_key,
            'default_label' => $request->default_label
        ]);

        return back()->with('success', 'Label created');
    }

    // update master
    public function updateMaster(Request $request, $id)
    {
        $master = FieldMaster::findOrFail($id);

        $master->update([
            'default_label' => $request->default_label
        ]);

        return back()->with('success', 'Label updated');
    }

    // delete master
    public function deleteMaster($id)
    {
        $master = FieldMaster::findOrFail($id);
        $fieldKey = $master->field_key;
        $master->delete();

        CompanyFieldLabel::where('field_key', $fieldKey)->delete();

        return back()->with('success', 'Label deleted');
    }

    // edit company labels
    public function editCompany($companyId)
    {
        $user = auth()->user();
        $isGlobalAdmin = $user->role_id == 8;

        // Safety Check: Ensure the user is only accessing their own company if not global admin
        if (!$isGlobalAdmin && $user->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        $masters = FieldMaster::all();
        $companyLabels = CompanyFieldLabel::where('company_id', $companyId)
            ->pluck('custom_label', 'field_key')
            ->toArray();

        return view('dynamiclabels.edit-company', compact('masters', 'companyLabels', 'companyId'));
    }

    // save overrides
    public function saveCompany(Request $request, $companyId)
    {
        $user = auth()->user();
        $isGlobalAdmin = $user->role_id == 8;

        // Safety Check
        if (!$isGlobalAdmin && $user->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        $labels = $request->labels ?? [];
        $upsert = [];
        $keysToKeep = [];

        foreach ($labels as $key => $label) {
            if ($label) {
                $upsert[] = [
                    'company_id' => $companyId,
                    'field_key' => $key,
                    'custom_label' => $label
                ];
                $keysToKeep[] = $key;
            }
        }

        // Delete any overrides for this company that were removed from the UI
        CompanyFieldLabel::where('company_id', $companyId)
            ->whereNotIn('field_key', $keysToKeep)
            ->delete();

        if (count($upsert) > 0) {
            CompanyFieldLabel::upsert(
                $upsert,
                ['company_id', 'field_key'],
                ['custom_label']
            );
        }

        // clear cache
        LabelService::clearCache($companyId);

        return back()->with('success', 'Labels updated');
    }
}
