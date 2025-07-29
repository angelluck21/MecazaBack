<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{

    public function Registro(Request $request){
        User::create([
            "name" => $request->Nombre,
            "email" => $request->Correo,
            "tel" => $request->Telefono,
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

    public function GetAll(User $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de carros exitosa"
        ],200);
    }

    public function Update(Request $request, User $user){
        $user->update([
            "name" => $request->Nombre,
            "email" => $request->Correo,
            "tel" => $request->Telefono,
            "password" => $request->Contrasena,
        ]);

        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function Destroy( User $user) {
        $user->delete();

        return response()->json([
            "message" => "Jugador eliminado Exitosamente!"
        ], 200);
    }
}
