<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Illuminate\Foundation\Http\FormRequest;
use Magdonia\LaravelFactories\Request\HasFactory;
use Magdonia\LaravelFactories\RequestFactory;

class NewRequest extends FormRequest
{
    use HasFactory;

    /**
     * @return RequestFactory<AnotherRequestFactory>
     */
    protected static function newFactory(): RequestFactory
    {
        return new AnotherRequestFactory();
    }
}
