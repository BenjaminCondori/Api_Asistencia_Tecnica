<?php

namespace App\Http\Controllers;

use App\Models\AssistanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssistanceRequestController extends Controller
{
    public function index()
    {
        $assistanceRequests = AssistanceRequest::all();
        return response()->json($assistanceRequests);
    }


    public function create()
    {
        //
    }

    public function getPendingAssistanceRequests() {
        $requests = AssistanceRequest::where('status', 'Pendiente')
        ->whereNull('technician_id')
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Solicitudes de asistencia pendientes.',
            'data' => $requests
        ], 200);
    }

    public function getAssistanceRequests(string $id)
    {
        $assistanceRequest = AssistanceRequest::find($id);
        if (!$assistanceRequest) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no existe.'
            ], 404);
        }

        $results = AssistanceRequest::where('customer_id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $results
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'problem_description' => 'required|string',
            'status' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'voice_note' => 'nullable|string|unique:customers,phone',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'customer_id' => 'required',
            'vehicle_id' => 'required',
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
            $urlAudio = null;

            if ($request->hasFile("photo")) {
                $file = $request->file("photo");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Elimina la extensión
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $pathImage = $file->storeAs('public/img/requests', $fileName);
                $urlImage = Storage::url($pathImage);
            }

            if ($request->hasFile("voice_note")) {
                $file = $request->file("voice_note");
                $originalFileName = $file->getClientOriginalName();
                $fileName = time() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME); // Elimina la extensión
                $fileName = Str::slug($fileName) . '.' . $file->getClientOriginalExtension();
                $pathAudio = $file->storeAs('public/img/requests', $fileName);
                $urlAudio = Storage::url($pathAudio);
            }

            $assistanceRequest = AssistanceRequest::create([
                'problem_description' => $request->input('problem_description'),
                'status' => $request->input('status'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'voice_note' => $urlAudio,
                'photo' => $urlImage,
                'customer_id' => $request->input('customer_id'),
                'vehicle_id' => $request->input('vehicle_id'),
            ]);

            // Todo salió bien, realizar la confirmación de la transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de asistencia registrado exitosamente.',
                'data' => $assistanceRequest,
            ], 201);
        } catch (\Exception $e) {
            // Algo salió mal, revertir la transacción
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ocurrió un error al registrar la solicitud.'
            ], 500);
        }

    }


    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
