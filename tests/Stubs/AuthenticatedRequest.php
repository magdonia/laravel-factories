<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Request\HasFactory;

class AuthenticatedRequest extends FormRequest
{
    use HasFactory;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
        ];
    }
}
