<?php

namespace Magdonia\LaravelFactories\Tests\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Concerns\HasRequestFactory;
use Magdonia\LaravelFactories\RequestFactory;
use Magdonia\LaravelFactories\Tests\Factories\Requests\AnotherRequestFactory;

class NewRequest extends FormRequest
{
    use HasRequestFactory;

    /**
     * @return RequestFactory<AnotherRequestFactory>
     */
    protected static function newFactory(): RequestFactory
    {
        return new AnotherRequestFactory();
    }
}
