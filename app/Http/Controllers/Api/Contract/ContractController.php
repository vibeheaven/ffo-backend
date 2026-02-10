<?php

namespace App\Http\Controllers\Api\Contract;

use App\Domain\Contract\Models\Contract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Tüm sözleşmeleri listele
     */
    public function index(): JsonResponse
    {
        $contracts = Contract::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $contracts,
        ]);
    }

    /**
     * Sadece aktif sözleşmeleri listele
     */
    public function active(): JsonResponse
    {
        $contracts = Contract::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contracts,
        ]);
    }

    /**
     * Belirli bir sözleşmeyi göster
     */
    public function show(Contract $contract): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $contract,
        ]);
    }

    /**
     * Yeni sözleşme oluştur (sadece root kullanıcılar)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|in:terms,privacy,gdpr,other',
            'version' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $contract = Contract::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $contract,
            'message' => 'Contract created successfully.',
        ], 201);
    }

    /**
     * Sözleşmeyi güncelle (sadece root kullanıcılar)
     */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|in:terms,privacy,gdpr,other',
            'version' => 'sometimes|required|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $contract->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $contract->fresh(),
            'message' => 'Contract updated successfully.',
        ]);
    }

    /**
     * Sözleşmeyi sil (sadece root kullanıcılar)
     */
    public function destroy(Contract $contract): JsonResponse
    {
        $contract->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contract deleted successfully.',
        ]);
    }

    /**
     * Kullanıcının sözleşmeyi kabul etmesi
     */
    public function accept(Request $request, Contract $contract): JsonResponse
    {
        $user = auth('api')->user();

        // Zaten kabul edilmiş mi kontrol et
        $existingAcceptance = $user->contracts()
            ->where('contract_id', $contract->id)
            ->wherePivot('accepted_at', '!=', null)
            ->exists();

        if ($existingAcceptance) {
            return response()->json([
                'status' => 'success',
                'message' => 'Contract already accepted.',
            ]);
        }

        // Sözleşmeyi kabul et
        $user->contracts()->syncWithoutDetaching([
            $contract->id => [
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contract accepted successfully.',
        ]);
    }

    /**
     * Kullanıcının kabul ettiği sözleşmeler
     */
    public function myAcceptances(): JsonResponse
    {
        $user = auth('api')->user();

        $acceptedContracts = $user->contracts()
            ->wherePivot('accepted_at', '!=', null)
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'title' => $contract->title,
                    'type' => $contract->type,
                    'version' => $contract->version,
                    'is_active' => $contract->is_active,
                    'accepted_at' => $contract->pivot->accepted_at,
                    'ip_address' => $contract->pivot->ip_address,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $acceptedContracts,
        ]);
    }
}
