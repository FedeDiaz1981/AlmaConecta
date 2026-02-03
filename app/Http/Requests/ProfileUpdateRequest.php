<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isClient = $user && $user->role === 'client';

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'document_type' => [$isClient ? 'required' : 'nullable', 'string', 'max:30'],
            'document_number' => [$isClient ? 'required' : 'nullable', 'string', 'max:50'],
            'phone' => [$isClient ? 'required' : 'nullable', 'string', 'max:30'],
        ];
    }
}
