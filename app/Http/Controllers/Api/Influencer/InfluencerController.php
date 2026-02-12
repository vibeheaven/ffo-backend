<?php

namespace App\Http\Controllers\Api\Influencer;

use App\Domain\Influencer\Models\Influencer;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InfluencerController extends Controller
{
    /**
     * Projeye ait influencer'ı göster
     */
    public function show(string $projectId): JsonResponse
    {
        $user = auth('api')->user();

        $project = Project::where('id', $projectId)
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Project not found.',
            ], 404);
        }

        $influencer = $project->influencer;

        if (!$influencer) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Influencer not found for this project.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'social_media_name' => $influencer->social_media_name,
                'face_description' => $influencer->face_description,
                'body_description' => $influencer->body_description,
                'character_description' => $influencer->character_description,
                'country_ethnicity' => $influencer->country_ethnicity,
                'face_images' => $influencer->face_image_urls,
                'body_images' => $influencer->body_image_urls,
                'created_at' => $influencer->created_at,
                'updated_at' => $influencer->updated_at,
            ],
        ]);
    }

    /**
     * Projeye influencer ekle
     */
    public function store(Request $request, string $projectId): JsonResponse
    {
        $user = auth('api')->user();

        $project = Project::where('id', $projectId)
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Project not found.',
            ], 404);
        }

        // Zaten influencer var mı kontrol et
        if ($project->influencer) {
            return response()->json([
                'error' => true,
                'errorCode' => 400,
                'message' => 'Project already has an influencer. Please update or delete the existing one.',
            ], 400);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'social_media_name' => 'required|string|max:255',
            'face_description' => 'required|string',
            'body_description' => 'required|string',
            'character_description' => 'required|string',
            'country_ethnicity' => 'required|string|max:255',
            'face_images' => 'required|array|min:5|max:20',
            'face_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'body_images' => 'required|array|min:5|max:20',
            'body_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // Influencer oluştur
        $influencer = new Influencer([
            'project_id' => $project->id,
            'name' => $validated['name'],
            'social_media_name' => $validated['social_media_name'],
            'face_description' => $validated['face_description'],
            'body_description' => $validated['body_description'],
            'character_description' => $validated['character_description'],
            'country_ethnicity' => $validated['country_ethnicity'],
        ]);

        $influencer->save();

        // Yüz resimlerini kaydet
        $faceImageNames = [];
        foreach ($request->file('face_images') as $image) {
            $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $path = $influencer->storage_folder . '/face/' . $filename;
            Storage::put($path, file_get_contents($image->getRealPath()));
            $faceImageNames[] = $filename;
        }

        // Fizik resimlerini kaydet
        $bodyImageNames = [];
        foreach ($request->file('body_images') as $image) {
            $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $path = $influencer->storage_folder . '/body/' . $filename;
            Storage::put($path, file_get_contents($image->getRealPath()));
            $bodyImageNames[] = $filename;
        }

        // Resim adlarını kaydet
        $influencer->face_images = $faceImageNames;
        $influencer->body_images = $bodyImageNames;
        $influencer->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'social_media_name' => $influencer->social_media_name,
                'face_images' => $influencer->face_image_urls,
                'body_images' => $influencer->body_image_urls,
            ],
            'message' => 'Influencer created successfully.',
        ], 201);
    }

    /**
     * Influencer güncelle
     */
    public function update(Request $request, string $projectId): JsonResponse
    {
        $user = auth('api')->user();

        $project = Project::where('id', $projectId)
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Project not found.',
            ], 404);
        }

        $influencer = $project->influencer;

        if (!$influencer) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Influencer not found for this project.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'social_media_name' => 'sometimes|required|string|max:255',
            'face_description' => 'sometimes|required|string',
            'body_description' => 'sometimes|required|string',
            'character_description' => 'sometimes|required|string',
            'country_ethnicity' => 'sometimes|required|string|max:255',
            'face_images' => 'sometimes|array|min:5|max:20',
            'face_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'body_images' => 'sometimes|array|min:5|max:20',
            'body_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        // Metin alanlarını güncelle
        $influencer->fill(array_intersect_key($validated, array_flip([
            'name',
            'social_media_name',
            'face_description',
            'body_description',
            'character_description',
            'country_ethnicity',
        ])));

        // Yüz resimleri güncellenecekse
        if ($request->hasFile('face_images')) {
            // Eski resimleri sil
            foreach ($influencer->face_images ?? [] as $filename) {
                Storage::delete($influencer->storage_folder . '/face/' . $filename);
            }

            // Yeni resimleri kaydet
            $faceImageNames = [];
            foreach ($request->file('face_images') as $image) {
                $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $path = $influencer->storage_folder . '/face/' . $filename;
                Storage::put($path, file_get_contents($image->getRealPath()));
                $faceImageNames[] = $filename;
            }
            $influencer->face_images = $faceImageNames;
        }

        // Fizik resimleri güncellenecekse
        if ($request->hasFile('body_images')) {
            // Eski resimleri sil
            foreach ($influencer->body_images ?? [] as $filename) {
                Storage::delete($influencer->storage_folder . '/body/' . $filename);
            }

            // Yeni resimleri kaydet
            $bodyImageNames = [];
            foreach ($request->file('body_images') as $image) {
                $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $path = $influencer->storage_folder . '/body/' . $filename;
                Storage::put($path, file_get_contents($image->getRealPath()));
                $bodyImageNames[] = $filename;
            }
            $influencer->body_images = $bodyImageNames;
        }

        $influencer->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'social_media_name' => $influencer->social_media_name,
                'face_images' => $influencer->face_image_urls,
                'body_images' => $influencer->body_image_urls,
            ],
            'message' => 'Influencer updated successfully.',
        ]);
    }

    /**
     * Influencer sil
     */
    public function destroy(string $projectId): JsonResponse
    {
        $user = auth('api')->user();

        $project = Project::where('id', $projectId)
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Project not found.',
            ], 404);
        }

        $influencer = $project->influencer;

        if (!$influencer) {
            return response()->json([
                'error' => true,
                'errorCode' => 404,
                'message' => 'Influencer not found for this project.',
            ], 404);
        }

        $influencer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Influencer deleted successfully.',
        ]);
    }
}
