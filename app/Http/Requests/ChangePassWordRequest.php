<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ChangePassWordRequest extends FormRequest
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
            'oldpass' => 'nullable|required_with:newpass',
            'newpass' => 'nullable|required_with:oldpass|required_with:newpass_confirmation|same:newpass_confirmation||different:oldpass',
            'newpass_confirmation' => 'nullable|required_with:newpass|different:oldpass',
        ];
    }
}
