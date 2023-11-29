<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }


    public function create()
    {
        //
    }

    public function getVehicles(string $id)
    {
        $cutomer = Customer::find($id);
        if (!$cutomer) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no existe.'
            ], 404);
        }

        $vehicles = Vehicle::where('customer_id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $vehicles
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'required|string',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'color' => 'required|string',
            'year' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            $urlImage = null;

            if ($request->hasFile("photo")) {
                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Elimina la extensión
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $pathImage = $file->storeAs('public/img/vehicles', $fileName);
                $urlImage = Storage::url($pathImage);
            }

            // Crear el vehiculo asociado al cliente
            $vehicle = Vehicle::create([
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'color' => $request->input('color'),
                'year' => $request->input('year'),
                'license_plate' => $request->input('license_plate'),
                'photo' => $urlImage,
                'customer_id' => $request->input('customer_id'),
            ]);

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo registrado exitosamente.',
                'data' => $vehicle,
            ], 201);
        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al registrar el vehículo.'
            ], 500);
        }
    }


    public function show(string $id)
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo no existe.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehículo obtenido exitosamente.',
            'data' => $vehicle
        ], 200);
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo no existe.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'model' => 'required|string',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'license_plate' => [
                'required',
                'string',
                Rule::unique('vehicles', 'license_plate')->ignore($request->vehicle_id), // Ignorar el vehiculo actual
            ],
            'color' => 'required|string',
            'year' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            $urlImage = null;

            // Actualizar la información del técnico
            $vehicle->update([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'specialty' => $request->input('specialty'),
                'phone' => $request->input('phone'),
            ]);

            // Actualizar la información del usuario
            $vehicle->update([
                'email' => $request->input('email'),
            ]);

            // Actualizar la imagen si se proporciona una nueva
            if ($request->hasFile("photo")) {
                $filePath = public_path($vehicle->photo);

                if (!is_null($vehicle->photo) && file_exists($filePath)) {
                    unlink($filePath);
                }

                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $pathImage = $file->storeAs('public/img/vehicles', $fileName);
                $urlImage = Storage::url($pathImage);
                $vehicle->update(['photo' => $urlImage]);
            }


            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo registrado exitosamente.',
                'data' => $vehicle,
            ], 201);
        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al registrar el vehículo.'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        $vehicle = Vehicle::find($id);
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'El vehículo no existe.'
            ], 404);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            // Eliminar la imagen asociada al vehiculo si existe
            if ($vehicle->photo) {
                $filePath = public_path($vehicle->photo);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar el usuario
            $vehicle->delete();

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado exitosamente.',
                'data' => $vehicle,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al eliminar el técnico.'
            ], 500);
        }
    }
}
