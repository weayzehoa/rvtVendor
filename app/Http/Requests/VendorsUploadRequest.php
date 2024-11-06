<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class VendorsUploadRequest extends FormRequest
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
            'img_cover' => 'nullable|image|max:10000',
            'img_logo' => 'nullable|image|max:10000',
            'img_site' => 'nullable|image|max:10000',
        ];
    }
}
