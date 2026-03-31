<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
// use App\Models\SiteAssignment; // Uncomment and adjust to your actual model name

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        // Assuming you are using standard Laravel Auth.
        // If you rely entirely on session('user'), you would use that instead.
        $user = Auth::user();

        // If your user object in session is different from standard Auth:
        // $user = User::find(session('user')->id);

        // Fetch the user's current site assignment.
        // Adjust the query to match your actual database relationships and models.
        // Example assuming a relationship or direct query:
        // $site_assign = SiteAssignment::where('user_id', $user->id)->where('status', 'active')->first();

        // Mocking the assignment variable for fallback (replace with actual DB call)
        $site_assign = null;

        return view('profile.my-profile', [
            'user' => $user,
            'site_assign' => $site_assign,
        ]);
    }

    public function edit(Request $request)
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            // Add other validation rules as needed
        ]);

        $user->name = $request->name;
        $user->contact = $request->contact;
        $user->dob = $request->dob;
        $user->gender = $request->gender;
        $user->code_name = $request->code_name;
        $user->address = $request->address;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $request->validate(['password' => 'confirmed|min:8']);
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }
}
