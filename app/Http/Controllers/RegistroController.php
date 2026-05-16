<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => $user,
        ]);
    }

    public function GetAll()
    {
        return response()->json([
            'data'    => User::all(),
            'message' => 'Consulta de usuarios exitosa',
        ], 200);
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
        $data = [];

        if ($request->filled('Nombre'))     $data['name']     = $request->Nombre;
        if ($request->filled('Rol'))        $data['rol']      = $request->Rol;
        if ($request->filled('Correo'))     $data['email']    = $request->Correo;
        if ($request->filled('Telefono'))   $data['tel']      = $request->Telefono;
        if ($request->filled('Contrasena')) $data['password'] = Hash::make($request->Contrasena);

        // Foto de perfil
        if ($request->hasFile('fotoperfil')) {
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
