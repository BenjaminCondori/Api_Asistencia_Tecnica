<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return response()->json($customers);
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
                            $query->where('type', 'cliente')
                                ->orWhereNull('type');
                        });
                }),
            ],
            'password' => 'required|string',
            'phone' => 'required|string|unique:customers,phone',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
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
                $path = '/img/customers/';
                $file->move(public_path($path), $fileName);
                $image = $path . $fileName;
            }

            // Crear el cliente asociado al usuario
            $customer = Customer::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'phone' => $request->input('phone'),
                'photo' => $image
            ]);

            // Crear el usuario
            $user = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'type' => $request->input('type'),
                'customer_id' => $customer->id,
            ]);

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('customer')->find($user->id);

            return response()->json([
                'message' => 'Usuario registrado exitosamente.',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al registrar el usuario.'
            ], 500);
        }
    }


    public function show(string $id)
    {
        $customer = Customer::with('users')->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'No se encontró el cliente.'
            ], 404);
        }

        return response()->json([
            'message' => 'Cliente obtenido exitosamente.',
            'customer' => $customer
        ]);
    }

    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        // Obtener el usuario y el cliente asociado por ID
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'No se encontró el cliente.'
            ], 404);
        }

        $user = User::where('customer_id', $customer->id)->first();

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
                            $query->where('type', 'cliente')
                                ->orWhereNull('type');
                        });
                })->ignore($user->id),
            ],
            'phone' => 'required|string|unique:customers,phone,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            // Actualizar la información del cliente
            $customer->update([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'phone' => $request->input('phone'),
            ]);

            // Actualizar la información del usuario
            $user->update([
                'email' => $request->input('email'),
            ]);

            // Actualizar la imagen si se proporciona una nueva
            if ($request->hasFile("photo")) {
                $filePath = public_path($customer->photo);

                if (!is_null($customer->photo) && file_exists($filePath)) {
                    unlink($filePath);
                }

                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $path = '/img/customers/';
                $file->move(public_path($path), $fileName);
                $customer->update(['photo' => $path . $fileName]);
            }

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            $user = User::with('customer')->find($user->id);

            return response()->json([
                'message' => 'Cliente actualizado exitosamente.',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al actualizar el cliente.'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        // Obtener el cliente asociado por ID
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'No se encontró el Cliente.'
            ], 404);
        }

        // Comenzar la transacción
        DB::beginTransaction();

        try {
            // Eliminar la imagen asociada al cliente si existe
            if ($customer->photo) {
                $filePath = public_path($customer->photo);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Eliminar el usuario
            $customer->delete();

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'message' => 'Cliente eliminado exitosamente.',
                'customer' => $customer,
            ], 200);

        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al eliminar el cliente.'
            ], 500);
        }
    }
}
