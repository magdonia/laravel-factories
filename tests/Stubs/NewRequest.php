<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Concerns\HasRequestFactory;
use Magdonia\LaravelFactories\RequestFactory;

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
