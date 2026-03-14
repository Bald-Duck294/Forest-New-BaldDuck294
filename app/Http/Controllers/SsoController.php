<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SsoController extends Controller
{
    public function redirect($app)
    {
        // dd('stop');

        $user = session()->get('user');

        if (!$user) {
            abort(401);
        }

        if (!in_array($app, ['tapaal', 'whatsapp'])) {
            abort(404);
        }

        $tempToken = Str::random(64);

        // $sso_session_row = DB::table('sso_sessions')->insert([
        //     'user_id' => $user->id,
        //     'client_app' => $app,
        //     'temp_token_hash' => hash('sha256', $tempToken),
        //     'expires_at' => now()->addMinutes(5),
        //     'created_at' => now(),
        // ]);

        if (in_array($app, ['whatsapp'])) {
            $ssoSessionId = DB::table('sso_sessions')->insertGetId([
                'user_id' => $user->id,
                'client_app' => $app,
                'temp_token_hash' => hash('sha256', $tempToken),
                'expires_at' => now()->addMinutes(5),
                'created_at' => now(),
            ]);
            session()->put('sessionId_whatsapp', $ssoSessionId);

        }
        ;



        $redirectUrls = [
            'tapaal' => 'https://tapaal.zpamravati.org/sso-login',
            'whatsapp' => 'http://localhost:3000/auth/sso',
        ];

        return redirect()->away(
            $redirectUrls[$app] . '?token=' . $tempToken
        );
    }
}
