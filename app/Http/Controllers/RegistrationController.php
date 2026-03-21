<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RegistrationData;
use App\CompanyDetails;
use App\ActivityLog;
use App\Exports\RegistrationExport;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Validator;
use Log;
use App\RoleIdList;

class RegistrationController extends Controller
{
    private $excel;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    /**
     * Display a listing of the registrations.
     */
    public function index(Request $request)
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $query = RegistrationData::where('company_id', $user->company_id)
            ->whereIn('role_id', [1, 2, 3, 7]);

        // Status Filtering
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status == 'registered' ? 1 : 0;
            $query->where('registrationFlag', $status);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'LIKE', "%{$search}%")
                    ->orWhere('lastName', 'LIKE', "%{$search}%")
                    ->orWhere('mobile', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy == 'name') {
            $query->orderBy('firstName', $sortOrder)->orderBy('lastName', $sortOrder);
        }
        else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $registrations = $query->paginate(15);
        return view('registration.index', compact('registrations'));
    }

    /**
     * Show the form for creating a new registration.
     */
    public function create()
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $roles = RoleIdList::whereIn('id', [1, 2, 3, 7])->orderBy('sequence', 'ASC')->get();

        return view('registration.create', compact('roles'));
    }

    /**
     * Store a newly created registration in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'role_id' => 'required|exists:role_id_list,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $company = CompanyDetails::find($user->company_id);

        RegistrationData::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
            'company_id' => $user->company_id,
            'company_name' => $company->name,
            'role_id' => $request->role_id,
            'registrationFlag' => 0 // Default to pending
        ]);

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Add Registration",
            'message' => "New registration added for " . $request->firstName . " by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('registrations.index')->with('success', 'Member added successfully.');
    }

    /**
     * Show the form for editing the specified registration.
     */
    public function edit($id)
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $registration = RegistrationData::where('company_id', $user->company_id)->where('id', $id)->firstOrFail();
        $roles = RoleIdList::whereIn('id', [1, 2, 3, 7])->orderBy('sequence', 'ASC')->get();

        return view('registration.edit', compact('registration', 'roles'));
    }

    /**
     * Update the specified registration in storage.
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'role_id' => 'required|exists:role_id_list,id',
            'registrationFlag' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $registration = RegistrationData::where('company_id', $user->company_id)->where('id', $id)->firstOrFail();

        $registration->update([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'department' => $request->department,
            'designation' => $request->designation,
            'role_id' => $request->role_id,
            'registrationFlag' => $request->registrationFlag,
        ]);

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Registration",
            'message' => "Registration updated for " . $request->firstName . " by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('registrations.index')->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified registration from storage.
     */
    public function destroy($id)
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $registration = RegistrationData::where('company_id', $user->company_id)->where('id', $id)->firstOrFail();
        $name = $registration->firstName;

        $registration->delete();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Registration",
            'message' => "Registration deleted for " . $name . " by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('registrations.index')->with('success', 'Member deleted successfully.');
    }

    /**
     * Export the registrations to Excel.
     */
    public function export()
    {
        /** @var \App\Users $user */
        $user = session('user');

        if (!$user || !in_array($user->role_id, [1, 8])) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $registrations = RegistrationData::where('company_id', $user->company_id)->get();
        $company = CompanyDetails::find($user->company_id);

        return $this->excel->download(
            new RegistrationExport($registrations, $company->name),
            'registrations_' . date('Y-m-d') . '.xlsx'
        );
    }
}
