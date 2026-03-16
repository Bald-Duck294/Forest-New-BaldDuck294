<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Attempt to find user by contact (phone number column in your database)
        $user = User::where('contact', $credentials['phone'])->first();

        if (!$user) {
            return back()->withErrors([
                'phone' => 'The provided phone number does not match our records.',
            ])->onlyInput('phone');
        }

        // Check if user is active (assuming isActive column exists, otherwise remove this check)
        if (isset($user->isActive) && $user->isActive == 0) {
            return back()->withErrors([
                'phone' => 'Your account is inactive. Please contact your administrator.',
            ])->onlyInput('phone');
        }

        // Check if user has login access (Guards with role_id 3 cannot login)
        if ($user->role_id == 3) {
            return back()->withErrors([
                'phone' => 'Guards do not have login access to this system.',
            ])->onlyInput('phone');
        }

        // Attempt authentication using contact column
        if (Auth::attempt(['contact' => $credentials['phone'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Store user in session
            $request->session()->put('user', Auth::user());

            // Store company_id in session if it exists
            if (Auth::user()->company_id) {
                $request->session()->put('company_id', Auth::user()->company_id);
            }

            // Redirect based on role
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
        ])->onlyInput('phone');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole($user)
    {
        // Global Super Admin
        if ($user->role_id == 8) {
            return redirect()->route('global.dashboard');
        }

        // Super Admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('analytics.executive');
        }

        // Admin
        if ($user->isAdmin()) {
            return redirect()->route('analytics.executive');
        }

        // Supervisor
        if ($user->isSupervisor()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
