<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Request\HasFactory;

class SimpleRequest extends FormRequest
{
    use HasFactory;

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
