<?php

namespace App\Http\Controllers;
use App\Models\Carros;
use App\Models\Estadoscarro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarrosController extends Controller
{
    /*
    public function Create(Request $request) {
        $carro = new Carros();
        $carro-> conductor = $request->Conductor;
        $carro-> telefono =$request->Telefono;
        $carro-> placa = $request->Placa;
        $carro-> asientos = $request->Asientos;
        $carro-> destino = $request->Destino;
        $carro-> horasalida =$request->Horasalida;
        $carro-> fecha = $request->Fecha;
        $carro->id_estados = $request->Estado;
        $carro->id_users = $request->Userid;

        $carro ->save();

        return response()->json([
            "message" => "carro agregado",
            "data" => $carro->load('estado'),
        ], 201);
    }
    */

    public function Create(Request $request) {
        // Validar que la imagen esté presente
        $request->validate([
            'Imagencarro' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Máximo 2MB
            'Conductor' => 'required|string',
            'Telefono' => 'required|string',
            'Placa' => 'required|string|unique:carros,placa',
            'Asientos' => 'required|integer|min:1|max:4',
            'Destino' => 'required|string',
            'Horasalida' => 'required|string',
            'Fecha' => 'required|date',
            'Estado' => 'required|integer',
            'Userid' => 'required|integer'
        ]);

        try {
            // Procesar la imagen
            if ($request->hasFile('Imagencarro')) {
                $imagen = $request->file('Imagencarro');

                // Generar nombre único para la imagen
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                $ok = Storage::disk('public')->putFileAs('carros', $imagen, $nombreImagen);
                if (!$ok || !Storage::disk('public')->exists("carros/$nombreImagen")) {
                    Log::error('No se encontró el archivo después de guardar', [
                        'rel' => "carros/$nombreImagen",
                        'disk' => 'public',
                        'exists' => Storage::disk('public')->exists("carros/$nombreImagen"),
                    ]);
                    abort(500, 'No se pudo guardar la imagen (permisos/disk).');
                }

                // Obtener la URL pública de la imagen
                $urlImagen = Storage::url("public/carros/$nombreImagen");
            }

            // Crear el carro
            $carro = new Carros();
            $carro->conductor = $request->Conductor;
            $carro->telefono = $request->Telefono;
            $carro->placa = $request->Placa;
            $carro->asientos = $request->Asientos;
            $carro->destino = $request->Destino;
            $carro->horasalida = $request->Horasalida;
            $carro->fecha = $request->Fecha;
            $carro->id_estados = $request->Estado;
            $carro->id_users = $request->Userid;

            // Guardar la ruta de la imagen
            if (isset($urlImagen)) {
                $carro->imagencarro = $urlImagen;
            }

            $carro->save();

            return response()->json([
                "message" => "Carro agregado exitosamente",
                "data" => $carro->load('estado'),
                "imagen" => $urlImagen ?? null
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al guardar el carro",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function GetAll(carros $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de carros exitosa"
        ],200);
    }

    public function Update(Request $request, Carros $agregarcarros){
        $agregarcarros->update([
            "conductor" => $request->Conductor,
            "imagencarro" => $request->Imagencarro,
            "placa" => $request->Placa,
            "asientos" => $request->Asientos,
            "destino" => $request->Destino,
            "horasalida" => $request->Horasalida,
            "fecha" => $request->Fecha,
        ]);

        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function updateestado(Request $request, Carros $carro){
        $carro->update([
            $carro->id_estados = $request->Estado,
            $carro->horasalida = $request->Horasalida,
            $carro->fecha = $request->Fecha
        ]);
        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);

    }

    public function Destroy(Carros $agregarcarros) {
        $agregarcarros->delete();

        return response()->json([
            "message" => "Jugador eliminado Exitosamente!"
        ], 200);
    }
}
