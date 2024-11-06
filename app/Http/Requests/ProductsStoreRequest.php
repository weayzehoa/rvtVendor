<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ProductsStoreRequest extends FormRequest
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
            'vendor_id' => 'required|integer',
            'category_id' => 'required|array|min:1',
            'sub_categories' => 'nullable|array|min:1',
            // 'sub_categories' => 'required_with:category_id',
            'unit_name_id' => 'required|integer',
            'from_country_id' => 'required|integer',
            'name' => 'required|max:64',
            'export_name_en' => 'nullable|max:128',
            'brand' => 'required|max:64',
            'serving_size' => 'required|max:255',
            'shipping_methods' => 'nullable',
            'price' => 'required|integer',
            'gross_weight' => 'required|integer',
            'net_weight' => 'nullable|integer',
            'title' => 'nullable|max:64',
            'intro' => 'nullable|max:500',
            'model_name' => 'nullable|max:32',
            'model_type' => 'required|digits_between:1,3',
            'is_tax_free' => 'required|boolean',
            'allow_country' => 'nullable',
            // 'specification' => 'nullable|max:10000',
            'verification_reason' => 'nullable|max:2000',
            // 'status' => 'required|integer|between:-9,2',
            'hotel_days' => 'required|integer|max:999',
            'airplane_days' => 'required|integer|max:999',
            'storage_life' => 'required|integer',
            'fake_price' => 'nullable|integer',
            'TMS_price' => 'nullable|integer',
            'vendor_price' => 'nullable|regex:/^\d*(\.\d{1,2})?$/',
            'unable_buy' => 'nullable|max:100',
            'pause_reason' => 'nullable|max:100',
            'tags' => 'nullable',
            'vendor_earliest_delivery_date' => 'nullable|date|max:10',
            'vendor_latest_delivery_date' => 'nullable|date|after:vendor_earliest_delivery_date',
            'ticket_price' => 'nullable|required_if:category_id,17|integer',
            'ticket_group' => 'nullable|required_if:category_id,17|string|max:40',
            'ticket_memo' => 'nullable|string|max:500',
        ];
    }
}
