<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Verifica el access_token de Google y autentica / registra al usuario.
     *
     * Acepta tanto id_token como access_token de Google.
     *
     * @bodyParam credential string required  Token devuelto por Google OAuth.
     */
    public function handleGoogleAuth(Request $request)
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        $credential = $request->credential;
        $googleData = null;

        // ── Intentar verificar como id_token ──────────────────────────────────
        $idTokenResponse = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $credential,
        ]);

        if ($idTokenResponse->successful()) {
            $googleData = $idTokenResponse->json();
        } else {
            // ── Intentar verificar como access_token ──────────────────────────
            $userInfoResponse = Http::withHeaders([
                'Authorization' => "Bearer {$credential}",
            ])->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($userInfoResponse->successful()) {
                $googleData = $userInfoResponse->json();
            }
        }

        if (!$googleData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token de Google inválido o expirado.',
            ], 401);
        }

        $email  = $googleData['email']      ?? null;
        $nombre = $googleData['name']       ?? ($googleData['given_name'] ?? 'Usuario Google');

        if (!$email) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se pudo obtener el correo de Google.',
            ], 400);
        }

        // ── Buscar o crear usuario ─────────────────────────────────────────────
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $nombre,
                'email'    => $email,
                'rol'      => 'usuario',
                'tel'      => '',
                'password' => bcrypt(Str::random(32)),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'user'   => $user,
        ]);
    }
}
