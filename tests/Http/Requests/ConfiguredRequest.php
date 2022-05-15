<?php

namespace Magdonia\LaravelFactories\Tests\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Concerns\HasRequestFactory;

class ConfiguredRequest extends FormRequest
{
    use HasRequestFactory;

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
        ];
    }
}
