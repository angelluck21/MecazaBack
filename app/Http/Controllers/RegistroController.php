<?php

namespace App\Http\Controllers;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    public function Registro(Request $request) {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'Nombre' => 'required|string|max:255',
            'Correo' => 'required|email|unique:registros,Correo',
            'Contrasena' => 'required|string|min:6',
        ], [
            'Nombre.required' => 'El nombre es obligatorio',
            'Correo.required' => 'El correo es obligatorio',
            'Correo.email' => 'El correo debe tener un formato válido',
            'Correo.unique' => 'Este correo ya está registrado',
            'Contrasena.required' => 'La contraseña es obligatoria',
            'Contrasena.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error de validación',
                'errores' => $validator->errors()
            ], 422);
        }

        try {
            // Crear el registro
            $registro = Registro::create([
                'Nombre' => $request->Nombre,
                'Correo' => $request->Correo,
                'Contrasena' => bcrypt($request->Contraseña), // Encriptar contraseña
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => 'Registro exitoso',
                'data' => [
                    'id' => $registro->id,
                    'nombre' => $registro->Nombre,
                    'correo' => $registro->Correo,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Método para obtener todos los registros
    public function index()
    {
        try {
            $registros = Registro::select('id', 'Nombre', 'Correo', 'created_at')
                                ->orderBy('created_at', 'desc')
                                ->get();

            return response()->json([
                'success' => true,
                'data' => $registros
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener registros',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Método para obtener un registro específico
    public function show($id)
    {
        try {
            $registro = Registro::select('id', 'Nombre', 'Correo', 'created_at')
                               ->find($id);

            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Registro no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $registro
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
