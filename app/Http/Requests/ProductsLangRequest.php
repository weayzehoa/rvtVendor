<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ProductsLangRequest extends FormRequest
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
            'product_id' => 'required',
            'name' => 'required|max:64',
            'lang' => 'required|max:5',
            'brand' => 'required|max:64',
            'serving_size' => 'required|max:255',
            'title' => 'required|max:64',
            'intro' => 'required|max:500',
            'model_name' => 'nullable|max:32',
            'specification' => 'required|max:5000',
            'unable_buy' => 'nullable|max:100',
        ];
    }
}
