<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    public function Create(Request $request)
    {
        User::create([
            "name" => $request->Nombre,
            "rol" => $request->Rol,
            "email" => $request->Correo,
            "tel" => $request->Telefono,
            "password" => Hash::make($request->Contrasena),
        ]);

        return response()->json([
            "message" => "Usuario Guardado exitosamente"
        ], 201);
    }

    public function LoginUsuario(Request $request) {
        $user = User::where("email", $request->Correo)->first();

        if (!$user || !Hash::check($request->Contrasena, $user->password)) {
            return response()->json([
                "status"=> "error",
                "message"=> "Error en las credenciales"
            ], 409);
        }

        $token = $user->createToken("token")->plainTextToken;
        return response()->json([
            "status"=> "success",
            "token"=> $token,
            "user" => $user  // Para que el frontend pueda acceder al rol
        ]);
    }

    public function traerUsuario(Request $request)
    {
        $usuario = $request->user(); // Devuelve el usuario autenticado
        return response()->json($usuario);
    }

    public function GetAll(User $user)
    {
        return response()->json([
            "data" => $user->get(),
            "message" => "Consulta de carros exitosa"
        ], 200);
    }

    public function Update(Request $request, User $user)
    {
        $user->update([
            "name" => $request->Nombre,
            "rol" => $request->Rol,
            "email" => $request->Correo,
            "tel" => $request->Telefono,
            "password" => $request->Contrasena,
        ]);

        return response()->json([
            "message" => "Actualizado exitosamente"
        ], 200);
    }

    public function Destroy(User $user)
    {
        $user->delete();

        return response()->json([
            "message" => "usuario eliminado Exitosamente!"
        ], 200);
    }

}
