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
     * Store a new comment for a profile.
     *
     * @param StoreCommentRequest $request
     * @param Profile $profile
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request, Profile $profile): JsonResponse
    {
        // Créer le commentaire
        $comment = Comment::create([
            'content' => $request->validated('content'),
            'profile_id' => $profile->id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès',
            'comment' => $comment->load('creator')
        ], Response::HTTP_CREATED);
    }
}
