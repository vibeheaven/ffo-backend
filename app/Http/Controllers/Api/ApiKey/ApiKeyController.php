<?php

namespace App\Http\Controllers\Api\ApiKey;

use App\Domain\ApiKey\Models\ApiKey;
use App\Domain\Service\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    /**
     * Kullanıcının tüm API keylerini listele
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();

        $apiKeys = $user->apiKeys()
            ->with('service:id,service_name,service_slug')
            ->get()
            ->map(function ($apiKey) {
                return [
                    'id' => $apiKey->id,
                    'api_key_name' => $apiKey->api_key_name,
                    'service' => $apiKey->service,
                    'created_at' => $apiKey->created_at,
                    'updated_at' => $apiKey->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $apiKeys,
        ]);
    }

    /**
     * Belirli bir API key'i göster (şifresi çözülmüş halde)
     */
    public function show(ApiKey $apiKey): JsonResponse
    {
        $user = auth('api')->user();

        // Sadece kendi API key'ini görebilir
        if ($apiKey->user_id !== $user->id) {
            return response()->json([
                'error' => true,
                'errorCode' => 403,
                'message' => 'Unauthorized access to this API key.',
            ], 403);
        }

        $apiKey->load('service:id,service_name,service_slug,service_form');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $apiKey->id,
                'api_key_name' => $apiKey->api_key_name,
                'api_key_value' => $apiKey->api_key_value,
                'api_secret_value' => $apiKey->api_secret_value,
                'others_data' => $apiKey->others_data,
                'service' => $apiKey->service,
                'created_at' => $apiKey->created_at,
                'updated_at' => $apiKey->updated_at,
            ],
        ]);
    }

    /**
     * Yeni API key oluştur
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'api_key_name' => 'required|string|max:255',
            'api_key_value' => 'required|string',
            'api_secret_value' => 'nullable|string',
            'others_data' => 'nullable|array',
        ]);

        // Servis aktif mi kontrol et
        $service = Service::find($validated['service_id']);
        if (!$service || !$service->is_active) {
            return response()->json([
                'error' => true,
                'errorCode' => 400,
                'message' => 'Service is not active or does not exist.',
            ], 400);
        }

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'api_key_name' => $validated['api_key_name'],
            'api_key_value' => $validated['api_key_value'],
            'api_secret_value' => $validated['api_secret_value'] ?? null,
            'others_data' => $validated['others_data'] ?? null,
        ]);

        $apiKey->load('service:id,service_name,service_slug');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $apiKey->id,
                'api_key_name' => $apiKey->api_key_name,
                'service' => $apiKey->service,
                'created_at' => $apiKey->created_at,
            ],
            'message' => 'API key created successfully.',
        ], 201);
    }

    /**
     * API key güncelle
     */
    public function update(Request $request, ApiKey $apiKey): JsonResponse
    {
        $user = auth('api')->user();

        // Sadece kendi API key'ini güncelleyebilir
        if ($apiKey->user_id !== $user->id) {
            return response()->json([
                'error' => true,
                'errorCode' => 403,
                'message' => 'Unauthorized access to this API key.',
            ], 403);
        }

        $validated = $request->validate([
            'api_key_name' => 'sometimes|required|string|max:255',
            'api_key_value' => 'sometimes|required|string',
            'api_secret_value' => 'nullable|string',
            'others_data' => 'nullable|array',
        ]);

        $apiKey->update($validated);

        $apiKey->load('service:id,service_name,service_slug');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $apiKey->id,
                'api_key_name' => $apiKey->api_key_name,
                'service' => $apiKey->service,
                'updated_at' => $apiKey->updated_at,
            ],
            'message' => 'API key updated successfully.',
        ]);
    }

    /**
     * API key sil
     */
    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $user = auth('api')->user();

        // Sadece kendi API key'ini silebilir
        if ($apiKey->user_id !== $user->id) {
            return response()->json([
                'error' => true,
                'errorCode' => 403,
                'message' => 'Unauthorized access to this API key.',
            ], 403);
        }

        $apiKey->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'API key deleted successfully.',
        ]);
    }
}
