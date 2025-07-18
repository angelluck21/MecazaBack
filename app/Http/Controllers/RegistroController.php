<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    //Metodo para afreafar usuario
    public function Registro(Request $request){
        User::create([
            "name" => $request->Nombre,
            "email" => $request->Correo,
            "password" => Hash::make($request->Contrasena),
        ]);

        return response()->json([
            "message" => "Usuario Guardado exitosamente"
        ],201);
    }

    public function Login(Request $request) {
        $user = User::where("email", $request->Correo)->first();

        if (Hash::check($request->Contrasena, $user->password)) {
            $token = $user->createToken("token")->plainTextToken;
            return response()->json([
                "status"=> "success",
                "token"=> $token,
            ]);
        }
        return response()->json([
            "status"=> "error",
            "message"=> "Error en las credenciales"
        ], 409);
    }
}
