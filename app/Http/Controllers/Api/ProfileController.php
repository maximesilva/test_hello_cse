<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProfileRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Enums\ProfilStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API de Gestion des Profils",
 *     description="API pour la gestion des profils et des commentaires",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 */
class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profiles",
     *     summary="Récupérer la liste des profils actifs",
     *     tags={"Profils"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des profils actifs",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="image_path", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $profiles = Profile::where('status', ProfilStatus::ACTIVE)
            ->latest()
            ->get();

        return ProfileResource::collection($profiles);
    }

    /**
     * @OA\Post(
     *     path="/api/profiles",
     *     summary="Créer un nouveau profil",
     *     tags={"Profils"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="name", type="string", example="Doe"),
     *          @OA\Property(property="first_name", type="string", example="John"),
     *          @OA\Property(property="image", type="string", format="binary"),
     *          @OA\Property(property="status", type="string", enum={"actif", "inactif", "en attente"}, example="actif")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profil créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="profile", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
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

    /**
     * Met à jour un profil existant.
     *
     * @OA\Put(
     *     path="/api/profiles/{profile}",
     *     summary="Modifier un profil",
     *     tags={"Profils"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="profile",
     *         in="path",
     *         required=true,
     *         description="UUID du profil",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="image", type="string", format="binary"),
     *              @OA\Property(property="status", type="string", enum={"actif", "inactif", "en attente"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="profile", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profil non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        $data = $request->validated();
        if ($request->has('image_path')) {
            $data['image_path'] = $request->validated('image_path');
        }
        $profile->update($data);
        return response()->json([
            'message' => 'Profil modifié avec succès',
            'profile' => $profile->fresh()->load('creator')
        ]);
    }

    /**
     * Supprime un profil existant.
     *
     * @OA\Delete(
     *     path="/api/profiles/{profile}",
     *     summary="Supprimer un profil",
     *     tags={"Profils"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="profile",
     *         in="path",
     *         required=true,
     *         description="UUID du profil",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profil non trouvé"
     *     )
     * )
     */
    public function destroy(Profile $profile): JsonResponse
    {
        $profile->delete();
        return response()->json([
            'message' => 'Profil supprimé avec succès'
        ]);
    }
} 