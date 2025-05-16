<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/profiles/{profile}/comments",
     *     summary="Ajouter un commentaire à un profil",
     *     tags={"Commentaires"},
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
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Un commentaire sur le profil")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commentaire ajouté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="comment", type="object")
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
     *         description="Erreur de validation ou commentaire déjà existant"
     *     )
     * )
     */
    public function store(StoreCommentRequest $request, Profile $profile): JsonResponse
    {
        // Créer le commentaire
        $comment = Comment::create([
            'content' => $request->validated('content'),
            'profile_id' => $profile->getKey(),
            'created_by' => $request->user()->getKey(),
        ]);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès',
            'comment' => $comment->load('creator')
        ], Response::HTTP_CREATED);
    }
}
