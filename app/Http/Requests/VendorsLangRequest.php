<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class VendorsLangRequest extends FormRequest
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
            'vendor_id' => 'nullable|numeric',
            'langId' =>'nullable|numeric',
            'lang' => 'required|string|max:3',
            'name' => 'required|max:40',
            'summary' => 'max:200',
            'description' => 'max:1000',
        ];
    }
}
