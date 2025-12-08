<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForumPostRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->status === 'active';
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => [
                'required',
                'string',
                'max:10000',
                function ($attribute, $value, $fail) {
                    if (preg_match('/https?:\/\/|www\.|\.com|\.org|\.net|\.io|\.co/i', $value)) {
                        $fail('Posts cannot contain links or URLs.');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'body.required' => 'Post content is required.',
            'body.max' => 'Post content cannot exceed 10,000 characters.',
            'title.required' => 'Post title is required.',
            'title.max' => 'Post title cannot exceed 255 characters.',
        ];
    }
}
