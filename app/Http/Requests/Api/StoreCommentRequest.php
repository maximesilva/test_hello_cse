<?php

namespace App\Http\Requests\Api;

use App\Models\Comment;
use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|min:3|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Le contenu du commentaire est requis',
            'content.min' => 'Le commentaire doit contenir au moins 3 caractères',
            'content.max' => 'Le commentaire ne peut pas dépasser 1000 caractères',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $profile = $this->route('profile');
            
            // Vérifier si l'administrateur a déjà commenté ce profil
            $existingComment = Comment::where('profile_id', $profile->id)
                ->where('created_by', $this->user()->id)
                ->first();

            if ($existingComment) {
                $validator->errors()->add(
                    'comment',
                    'Vous avez déjà commenté ce profil'
                );
            }
        });
    }
}
