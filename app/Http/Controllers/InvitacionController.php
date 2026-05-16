<?php

namespace App\Http\Controllers;

use App\Models\InvitacionConductor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvitacionController extends Controller
{
    // Admin crea la invitación y dispara el webhook a N8N
    public function Invitar(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        // Invalidar invitaciones previas no usadas para este email
        InvitacionConductor::where('email', $request->email)
            ->where('usado', false)
            ->delete();

        $token = Str::random(64);

        InvitacionConductor::create([
            'email'      => $request->email,
            'token'      => $token,
            'usado'      => false,
            'creado_por' => $request->user()->id_users,
            'expires_at' => now()->addHours(48),
        ]);

        $link = env('FRONTEND_URL', 'http://localhost:5173') . '/registrar-conductor?token=' . $token;

        // Llamar webhook N8N para que envíe el email
        $webhookUrl = env('N8N_WEBHOOK_INVITACION');
        if ($webhookUrl) {
            try {
                Http::timeout(5)->post($webhookUrl, [
                    'email'        => $request->email,
                    'link'         => $link,
                    'token'        => $token,
                    'nombre_admin' => $request->user()->name,
                    'expires_en'   => '48 horas',
                ]);
            } catch (\Exception $e) {
                Log::error('Error al llamar webhook N8N invitación', ['error' => $e->getMessage()]);
                // No falla la request aunque N8N no responda
            }
        }

        return response()->json([
            'message' => 'Invitación enviada correctamente',
            'email'   => $request->email,
        ], 201);
    }

    // Valida que el token sea válido (llamado desde el front antes de mostrar el form)
    public function ValidarToken(string $token)
    {
        $invitacion = InvitacionConductor::where('token', $token)->first();

        if (!$invitacion || !$invitacion->isValida()) {
            return response()->json([
                'valid'   => false,
                'message' => 'El enlace no es válido o ya fue utilizado.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'email' => $invitacion->email,
        ]);
    }

    // El conductor llena el formulario y crea su cuenta
    public function RegistrarConductor(Request $request, string $token)
    {
        $invitacion = InvitacionConductor::where('token', $token)->first();

        if (!$invitacion || !$invitacion->isValida()) {
            return response()->json([
                'message' => 'El enlace no es válido o ya fue utilizado.',
            ], 422);
        }

        $request->validate([
            'Nombre'     => 'required|string|max:255',
            'Contrasena' => 'required|string|min:6',
            'Telefono'   => 'required|string|max:20',
        ]);

        $user = User::create([
            'name'     => $request->Nombre,
            'rol'      => 'conductor',
            'email'    => $invitacion->email,
            'tel'      => $request->Telefono,
            'password' => Hash::make($request->Contrasena),
        ]);

        // Marcar invitación como usada
        $invitacion->update(['usado' => true]);

        $authToken = $user->createToken('token')->plainTextToken;

        return response()->json([
            'message' => 'Cuenta de conductor creada exitosamente',
            'token'   => $authToken,
            'user'    => $user,
        ], 201);
    }
}
