<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class VendorsRequest extends FormRequest
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
            // 'name' => 'required|max:40',
            // 'company' => 'required|max:64',
            // 'vat_number' => 'required|numeric|digits:8',
            // 'boss' => 'required|max:20',
            'contact_person' => 'required|max:20',
            'tel' => 'required|numeric|digits_between:7,20',
            'fax' => 'nullable|max:20',
            // 'categories' => 'required|array|min:1',
            'address' => 'required|max:255',
            // 'shipping_setup' => 'required|numeric|max:99999999',
            // 'shipping_verdor_percent' => 'required|numeric|max:100',
            'summary' => 'max:500',
            'description' => 'max:1000',
            'factory_address' => 'required|max:255',
            // 'product_sold_country' => 'required',
            // 'service_fee.percent' => 'numeric|max:100',
            'langs.en.name' => 'max:40',
            'langs.en.summary' => 'max:500',
            'langs.en.description' => 'max:1000',
        ];
    }
}
