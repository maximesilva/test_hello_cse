<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Enums\ProfilStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * Display a listing of active profiles.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $profiles = Profile::where('status', ProfilStatus::ACTIVE)
            ->latest()
            ->get();

        return ProfileResource::collection($profiles);
    }

    /**
     * Store a newly created profile in storage.
     *
     * @param StoreProfileRequest $request
     * @return JsonResponse
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        $profile = Profile::create([
            'name' => $request->validated('name'),
            'first_name' => $request->validated('first_name'),
            'image_path' => $request->validated('image_path'),
            'status' => $request->validated('status'),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Profil créé avec succès',
            'profile' => $profile->load('creator')
        ], Response::HTTP_CREATED);
    }
} 