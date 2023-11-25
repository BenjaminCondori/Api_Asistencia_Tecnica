<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TechnicianController extends Controller
{
    public function index()
    {
        //
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($request) {
                    // Verificar si ya existe un usuario con el mismo email y tipo
                    return $query->where('email', $request->input('email'))
                        ->where(function ($query) {
                            $query->where('type', 'tecnico')
                                ->orWhereNull('type');
                        });
                }),
            ],
            'password' => 'required|string',
            'phone' => 'required|string|unique:technicians,phone',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'specialty' => 'required|string',
            'type' => 'required|string',
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
            $image = null;

            if ($request->hasFile("photo")) {
                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Elimina la extensión
                $fileName = Str::slug($fileName). '.' . $file->getClientOriginalExtension();
                $path = '/img/technicians/';
                $file->move(public_path($path), $fileName);
                $image = $path . $fileName;
            }

            // Crear el técnico asociado al usuario
            $technician = Technician::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'specialty' => $request->input('specialty'),
                'phone' => $request->input('phone'),
                'workshop_id' => $request->input('workshop_id'),
                'status' => 'Disponible', // Por defecto
                'photo' => $image,
            ]);

            // Crear el usuario
            $user = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'type' => $request->input('type'),
                'technician_id' => $technician->id,
            ]);

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('technician')->find($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente.',
                'data' => $user,
            ], 201);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al registrar el usuario.'
            ], 500);
        }
    }


    public function show(string $id)
    {
        $technician = Technician::with('users')->find($id);

        if (!$technician) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el técnico.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Técnico obtenido exitosamente.',
            'data' => $technician
        ]);
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        // Obtener el usuario y el cliente asociado por ID
        $technician = Technician::find($id);

        if (!$technician) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el técnico.'
            ], 404);
        }

        $user = User::where('technician_id', $technician->id)->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'specialty' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($request) {
                    // Verificar si ya existe un usuario con el mismo email y tipo
                    return $query->where('email', $request->input('email'))
                        ->where(function ($query) {
                            $query->where('type', 'tecnico')
                                ->orWhereNull('type');
                        });
                })->ignore($user->id),
            ],
            'phone' => 'required|string|unique:technicians,phone,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
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
            // Actualizar la información del técnico
            $technician->update([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'specialty' => $request->input('specialty'),
                'phone' => $request->input('phone'),
            ]);

            // Actualizar la información del usuario
            $user->update([
                'email' => $request->input('email'),
            ]);

            // Actualizar la imagen si se proporciona una nueva
            if ($request->hasFile("photo")) {
                $filePath = public_path($technician->photo);

                if (!is_null($technician->photo) && file_exists($filePath)) {
                    unlink($filePath);
                }

                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $path = '/img/technicians/';
                $file->move(public_path($path), $fileName);
                $technician->update(['photo' => $path . $fileName]);
            }

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('technician')->find($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Técnico actualizado exitosamente.',
                'data' => $user,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al actualizar el técnico.'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        // Obtener el técnico asociado por ID
        $technician = Technician::find($id);

        if (!$technician) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el Técnico.'
            ], 404);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            // Eliminar la imagen asociada al técnico si existe
            if ($technician->photo) {
                $filePath = public_path($technician->photo);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar el usuario
            $technician->delete();

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Técnico eliminado exitosamente.',
                'data' => $technician,
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
