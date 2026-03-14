<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\SsoHelper;

class SsoAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $token = $request->input('token');
        \Log::info('SSO Token received: ' . $token);

        if (!$token) {
            return response()->json([
                'message' => 'Token missing'
            ], 400);
        }

        $hashedToken = hash('sha256', $token);

        // 1️⃣ Find valid SSO session
        $ssoSession = DB::table('sso_sessions')
            ->where('temp_token_hash', $hashedToken)
            // ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$ssoSession) {
            return response()->json([
                'message' => 'Invalid or expired token',
                'ssoSession' => $ssoSession,
                'hashedToken' => $hashedToken,
                'token' => $token,
                // 'user' => $user
            ], 401);
        }

        // 2️⃣ Fetch user
        $user = DB::table('users')
            ->where('id', $ssoSession->user_id)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // 3️⃣ Mark token as used (VERY IMPORTANT)
        DB::table('sso_sessions')
            ->where('id', $ssoSession->id)
            ->update([
                'used_at' => now()
            ]);

        // 4️⃣ (OPTIONAL) Generate JWT (2 hours)
        $jwt = SsoHelper::generate([
            'id' => $user->id,
            'phone' => $user->contact,
            'email' => $user->email ?? null,
            'role_id' => $user->role_id ?? null,
            'company_id' => $user->company_id ?? null,
            'iss' => 'FSM'
        ], 7200);

        // 5️⃣ Respond to child dashboard
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'role_id' => $user->role_id ?? null,
                'phone' => $user->contact ?? null,
                'company_id' => $user->company_id ?? null,
            ],
            'token' => $jwt,
            'expires_in' => 7200,
            'ssoSessionId' => $ssoSession->id
        ]);
    }

    public function sessionStatus(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return response()->json([
                'valid' => false,
                'message' => 'No token provided'
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            // Decode JWT using same secret
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid token format');
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );

            if (!$payload || !isset($payload['iss']) || $payload['iss'] !== 'FSM') {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid issuer'
                ], 401);
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token expired'
                ], 401);
            }

            // Optional: check user still exists / active
            $user = DB::table('users')
                ->where('id', $payload['id'])
                ->first();

            if (!$user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'User not found'
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'company_id' => $user->company_id,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid token'
            ], 401);
        }
    }
    // Add to SsoAuthController.php
    public function validateUser(Request $request)
    {
        $sessionId = $request->input('sessionId');
        $userId = $request->input('userId');

        // Check if user has active session
        $hasActiveSession = DB::table('sso_sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->whereNotNull('used_at')      // Not consumed
            ->exists();

        // $hasActiveSession = DB::table('sso_sessions')
        //     ->where('id', $sessionId)
        //     ->where('expires_at', '>', now())
        //     ->latest('created_at')  // Most recent first
        //     ->exists();

        // $user = DB::table('users')
        //     ->where('id', $userId)
        //     ->whereNull('used_at')  // ✅ ADD THIS
        //     ->first();

        $user = DB::table('users')
            ->where('id', $userId)
            ->first();  // ✅ No is_active if column doesn't exist

        return response()->json([
            'valid' => $hasActiveSession && $user,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ] : null,
            'hasActiveSession' => $hasActiveSession,
            'userExists' => !!$user,
            'userId' => $userId,
            'sessionId' => $sessionId
        ]);
    }


}
