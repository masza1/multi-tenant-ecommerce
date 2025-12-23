<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        // Build unique rules with proper ID ignoring
        $slugRule = 'unique:products,slug';
        $skuRule = 'unique:products,sku';

        if ($productId) {
            $slugRule .= "," . $productId;
            $skuRule .= "," . $productId;
        }

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'original_price' => ['nullable', 'numeric', 'min:0.01', 'gte:price'],
            'sku' => ['required', 'string', 'max:100', $skuRule],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
            'images' => ['array'],
            'images.*.image_url' => ['required_with:images', 'url'],
            'images.*.alt_text' => ['nullable', 'string', 'max:255'],
            'images.*.is_primary' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'The selected category is invalid',
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
            'price.min' => 'Price must be greater than 0',
            'original_price.gte' => 'Original price must be greater than or equal to the sale price',
            'sku.required' => 'SKU (Stock Keeping Unit) is required',
            'sku.unique' => 'This SKU is already in use',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a whole number',
        ];
    }
}
