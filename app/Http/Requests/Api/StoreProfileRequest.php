<?php

namespace App\Http\Requests\Api;

use App\Enums\ProfilStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreProfileRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
            'status' => ['required', new Enum(ProfilStatus::class)],
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
            'name.required' => 'Le nom est requis',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères',
            'first_name.required' => 'Le prénom est requis',
            'first_name.max' => 'Le prénom ne peut pas dépasser 255 caractères',
            'image.required' => 'L\'image est requise',
            'image.image' => 'Le fichier doit être une image',
            'image.mimes' => 'L\'image doit être au format jpeg, png, jpg ou gif',
            'image.max' => 'L\'image ne peut pas dépasser 2MB',
            'status.required' => 'Le statut est requis',
            'status.enum' => 'Le statut n\'est pas valide',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if ($this->hasFile('image')) {
            $file = $this->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            
            // Stocker l'image dans le dossier public/profiles
            $path = $file->storeAs('profiles', $filename, 'public');
            
            // Ajouter le chemin de l'image aux données validées
            $this->merge(['image_path' => $path]);
        }
    }
}
