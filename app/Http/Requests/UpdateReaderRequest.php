<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReaderRequest extends FormRequest
{
    /**
     * Determina se l'utente è autorizzato a fare questa richiesta.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ottieni le regole di validazione che si applicano alla richiesta.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Personalizza i messaggi di errore di validazione (opzionale).
     */
    public function messages()
    {
        return [
            'image.required' => 'L\'immagine è obbligatoria.',
            'image.image' => 'Il file caricato deve essere un\'immagine.',
            'image.mimes' => 'L\'immagine deve essere nei formati: jpeg, png, jpg.',
            'image.max' => 'L\'immagine non deve superare i 2MB.',
        ];
    }
}
