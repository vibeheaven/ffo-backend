<?php

namespace App\Http\Controllers\Api\Service;

use App\Domain\Service\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Tüm servisleri listele (sadece root kullanıcılar)
     */
    public function index(): JsonResponse
    {
        $services = Service::orderBy('service_name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $services,
        ]);
    }

    /**
     * Aktif servisleri listele
     */
    public function active(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('service_name')
            ->get(['id', 'service_name', 'service_slug', 'description']);

        return response()->json([
            'status' => 'success',
            'data' => $services,
        ]);
    }

    /**
     * Servis detayı ve formu göster
     */
    public function show(Service $service): JsonResponse
    {
        if (!$service->is_active) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Service not found or inactive.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $service,
        ]);
    }

    /**
     * Yeni servis oluştur (sadece root kullanıcılar)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255|unique:services',
            'service_slug' => 'required|string|max:255|unique:services',
            'description' => 'nullable|string',
            'service_form' => 'required|array',
            'service_form.*.name' => 'required|string',
            'service_form.*.label' => 'required|string',
            'service_form.*.type' => 'required|string|in:text,password,textarea,select,number',
            'service_form.*.required' => 'boolean',
            'service_form.*.placeholder' => 'nullable|string',
            'service_form.*.options' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $service = Service::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $service,
            'message' => 'Service created successfully.',
        ], 201);
    }

    /**
     * Servisi güncelle (sadece root kullanıcılar)
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'service_name' => 'sometimes|required|string|max:255|unique:services,service_name,' . $service->id,
            'service_slug' => 'sometimes|required|string|max:255|unique:services,service_slug,' . $service->id,
            'description' => 'nullable|string',
            'service_form' => 'sometimes|required|array',
            'service_form.*.name' => 'required|string',
            'service_form.*.label' => 'required|string',
            'service_form.*.type' => 'required|string|in:text,password,textarea,select,number',
            'service_form.*.required' => 'boolean',
            'service_form.*.placeholder' => 'nullable|string',
            'service_form.*.options' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $service->fresh(),
            'message' => 'Service updated successfully.',
        ]);
    }

    /**
     * Servisi sil (sadece root kullanıcılar)
     */
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully.',
        ]);
    }
}
