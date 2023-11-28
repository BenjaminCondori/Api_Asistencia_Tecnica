<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkshopController extends Controller
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
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'address' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($request) {
                    // Verificar si ya existe un usuario con el mismo email y tipo
                    return $query->where('email', $request->input('email'))
                        ->where(function ($query) {
                            $query->where('type', 'taller')
                                ->orWhereNull('type');
                        });
                }),
            ],
            'password' => 'required|string',
            'phone' => 'required|string|unique:workshops,phone',
            'photo' => 'nullable|mimes:jpeg,png,jpg|max:10240',
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
            $urlImage = null;

            if ($request->hasFile("photo")) {
                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Elimina la extensión
                $fileName = Str::slug($fileName). '.' . $file->getClientOriginalExtension();
                $pathImage = $file->storeAs('public/img/workshops', $fileName);
                $urlImage = Storage::url($pathImage);
            }

            // Crear el taller asociado al usuario
            $workshop = Workshop::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
                'photo' => $urlImage
            ]);

            // Crear el usuario
            $user = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'type' => $request->input('type'),
                'workshop_id' => $workshop->id,
            ]);

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('workshop')->find($user->id);

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
        $workshop = Workshop::with('users')->find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el taller.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Taller obtenido exitosamente.',
            'data' => $workshop,
        ], 200);
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        // Obtener el usuario y el taller asociado por ID
        $workshop = Workshop::find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el taller.'
            ], 404);
        }

        $user = User::where('workshop_id', $workshop->id)->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'address' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($request) {
                    // Verificar si ya existe un usuario con el mismo email y tipo
                    return $query->where('email', $request->input('email'))
                        ->where(function ($query) {
                            $query->where('type', 'taller')
                                ->orWhereNull('type');
                        });
                })->ignore($user->id),
            ],
            'phone' => 'required|string|unique:workshops,phone,' . $id,
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
            // Actualizar la información del cliente
            $workshop->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
            ]);

            // Actualizar la información del usuario
            $user->update([
                'email' => $request->input('email'),
            ]);

            // Actualizar la imagen si se proporciona una nueva
            if ($request->hasFile("photo")) {
                $filePath = public_path($workshop->photo);

                if (!is_null($workshop->photo) && file_exists($filePath)) {
                    unlink($filePath);
                }

                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $pathImage = $file->storeAs('public/img/workshops', $fileName);
                $urlImage = Storage::url($pathImage);
                $workshop->update(['photo' => $urlImage]);
            }

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('workshop')->find($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Taller actualizado exitosamente.',
                'data' => $user,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al actualizar el taller.'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        // Obtener el taller asociado por ID
        $workshop = Workshop::find($id);

        if (!$workshop) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el taller.'
            ], 404);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            // Eliminar la imagen asociada al cliente si existe
            if ($workshop->photo) {
                $filePath = public_path($workshop->photo);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar el usuario
            $workshop->delete();

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taller eliminado exitosamente.',
                'data' => $workshop,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al eliminar el taller.'
            ], 500);
        }
    }
}
