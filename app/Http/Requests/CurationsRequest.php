<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class CurationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category' => 'required|max:10',
            'type' => 'required|max:20',
            'main_title' => 'required|max:255',
            'sub_title' => 'nullable|max:255',
            'caption' => 'nullable|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'main_title_background' => 'nullable|max:40',
            'background_color' => 'nullable|max:40',
            'background_css' => 'nullable|max:255',
            'background_image' => 'nullable|image',
            'url' => 'nullable|max:255',
        ];
    }
}
