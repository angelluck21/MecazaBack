<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Carros;
use App\Models\Reservarviaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Rules\RealImageMime;

class RegistroController extends Controller
{
    public function Create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre'    => 'required|string|max:255',
            'Correo'    => 'required|email|unique:users,email',
            'Contrasena'=> 'required|string|min:3',
            'Telefono'  => 'required|string|max:20',
            'Rol'       => 'required|in:usuario,conductor,administrador',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'     => $request->Nombre,
            'rol'      => $request->Rol,
            'email'    => $request->Correo,
            'tel'      => $request->Telefono,
            'password' => Hash::make($request->Contrasena),
        ]);

        return response()->json([
            'message'  => 'Usuario registrado exitosamente',
            'id_users' => $user->id_users,
            'user'     => $user,
        ], 201);
    }

    public function LoginUsuario(Request $request)
    {
        $user = User::where('email', $request->Correo)->first();

        if (!$user || !Hash::check($request->Contrasena, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'user'   => $user,
        ]);
    }

    public function Logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'success']);
    }

    public function GetAll()
    {
        return response()->json(User::orderBy('created_at', 'desc')->paginate(10));
    }

    public function Show(User $user)
    {
        return response()->json([
            'data'    => $user,
            'message' => 'Usuario encontrado',
        ], 200);
    }

    public function Update(Request $request, User $user)
    {
        $authUser = $request->user();
        $isAdmin  = in_array(strtolower($authUser->rol ?? ''), ['admin', 'administrador']);

        if (!$isAdmin && $authUser->id_users !== $user->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $data = [];

        if ($request->filled('Nombre'))     $data['name']     = $request->Nombre;
        if ($request->filled('Rol'))        $data['rol']      = $request->Rol;
        if ($request->filled('Correo'))     $data['email']    = $request->Correo;
        if ($request->filled('Telefono'))   $data['tel']      = $request->Telefono;
        if ($request->filled('Contrasena')) $data['password'] = Hash::make($request->Contrasena);

        // Foto de perfil
        if ($request->hasFile('fotoperfil')) {
            $request->validate([
                'fotoperfil' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048', new RealImageMime()],
            ]);
            // Eliminar foto anterior si existe
            if ($user->fotoperfil) {
                $oldPath = ltrim(str_replace('/storage', '', parse_url($user->fotoperfil, PHP_URL_PATH)), '/');
                Storage::disk('public')->delete($oldPath);
            }

            $imagen       = $request->file('fotoperfil');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('usuarios', $imagen, $nombreImagen);
            $data['fotoperfil'] = Storage::url("usuarios/{$nombreImagen}");
        }

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user'    => $user->fresh(),
        ], 200);
    }

    public function PerfilConductor($id_users)
    {
        $user = User::find($id_users);
        if (!$user) {
            return response()->json(['message' => 'Conductor no encontrado'], 404);
        }

        $carrosIds = Carros::where('id_users', $id_users)->pluck('id_carros');

        $reservas = Reservarviaje::whereIn('id_carros', $carrosIds)
            ->where('estado', 'completada')
            ->get();

        $totalViajes      = $reservas->count();
        $conCalificacion  = $reservas->filter(fn($r) => $r->calificacion !== null);
        $promedio         = $conCalificacion->count() > 0
            ? round($conCalificacion->avg('calificacion'), 1)
            : null;

        $resenas = $reservas
            ->filter(fn($r) => $r->calificacion !== null && $r->comentario_calificacion)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values()
            ->map(fn($r) => [
                'calificacion' => $r->calificacion,
                'comentario'   => $r->comentario_calificacion,
                'fecha'        => $r->updated_at?->format('d/m/Y') ?? '',
            ]);

        return response()->json([
            'data' => [
                'id_users'             => $user->id_users,
                'nombre'               => $user->name,
                'fotoperfil'           => $user->fotoperfil,
                'total_viajes'         => $totalViajes,
                'promedio_estrellas'   => $promedio,
                'total_calificaciones' => $conCalificacion->count(),
                'resenas'              => $resenas,
            ],
        ]);
    }

    public function PerfilUsuario($id_users)
    {
        $user = User::find($id_users);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $reservas = Reservarviaje::where('id_users', $id_users)
            ->where('estado', 'completada')
            ->get();

        $totalViajes     = $reservas->count();
        $conCalificacion = $reservas->filter(fn($r) => $r->calificacion_conductor !== null);
        $promedio        = $conCalificacion->count() > 0
            ? round($conCalificacion->avg('calificacion_conductor'), 1)
            : null;

        $resenas = $reservas
            ->filter(fn($r) => $r->calificacion_conductor !== null && $r->comentario_conductor)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values()
            ->map(fn($r) => [
                'calificacion' => $r->calificacion_conductor,
                'comentario'   => $r->comentario_conductor,
                'fecha'        => $r->updated_at?->format('d/m/Y') ?? '',
            ]);

        return response()->json([
            'data' => [
                'id_users'             => $user->id_users,
                'nombre'               => $user->name,
                'total_viajes'         => $totalViajes,
                'promedio_estrellas'   => $promedio,
                'total_calificaciones' => $conCalificacion->count(),
                'resenas'              => $resenas,
            ],
        ]);
    }

    public function ExportarMisDatos(Request $request)
    {
        $user = $request->user();

        $reservas = Reservarviaje::with(['carro.precioviaje'])
            ->where('id_users', $user->id_users)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id'                  => $r->id_reservarviajes,
                'estado'              => $r->estado,
                'asiento'             => $r->asiento,
                'ubicacion_recogida'  => $r->ubicacion,
                'origen'              => $r->carro?->precioviaje?->origen,
                'destino'             => $r->carro?->precioviaje?->destino,
                'precio'              => $r->carro?->precioviaje?->precio,
                'fecha_viaje'         => $r->carro?->fecha,
                'hora_salida'         => $r->carro?->horasalida,
                'calificacion'        => $r->calificacion,
                'comentario'          => $r->comentario_calificacion,
                'motivo_cancelacion'  => $r->motivo_cancelacion,
                'cancelado_por'       => $r->cancelado_por,
                'fecha_cancelacion'   => $r->fecha_cancelacion?->toIso8601String(),
                'creada'              => $r->created_at?->toIso8601String(),
                'actualizada'         => $r->updated_at?->toIso8601String(),
            ]);

        $facturas = Faturaviaje::where('id_users', $user->id_users)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($f) => [
                'numero_factura' => $f->numero_factura,
                'origen'         => $f->origen,
                'destino'        => $f->destino,
                'subtotal'       => $f->subtotal,
                'descuento'      => $f->impuesto,
                'total'          => $f->total,
                'emitida'        => $f->created_at?->toIso8601String(),
            ]);

        $notificaciones = \App\Models\Notificacion::where('id_users', $user->id_users)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($n) => [
                'titulo'  => $n->titulo,
                'mensaje' => $n->mensaje,
                'tipo'    => $n->tipo,
                'leida'   => $n->leida,
                'fecha'   => $n->created_at?->toIso8601String(),
            ]);

        $export = [
            'exportado_el'   => now()->toIso8601String(),
            'perfil'         => [
                'nombre'       => $user->name,
                'correo'       => $user->email,
                'telefono'     => $user->tel ?? null,
                'rol'          => $user->rol ?? null,
                'registrado'   => $user->created_at?->toIso8601String(),
            ],
            'reservas'        => $reservas,
            'facturas'        => $facturas,
            'notificaciones'  => $notificaciones,
        ];

        return response()->json($export);
    }

    public function Destroy(User $user)
    {
        // Eliminar foto si existe
        if ($user->fotoperfil) {
            $oldPath = ltrim(str_replace('/storage', '', parse_url($user->fotoperfil, PHP_URL_PATH)), '/');
            Storage::disk('public')->delete($oldPath);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
        ], 200);
    }
}
