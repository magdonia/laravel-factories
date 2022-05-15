<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Requests;

use Magdonia\LaravelFactories\RequestFactory;
use Magdonia\LaravelFactories\Tests\Http\Requests\SimpleRequest;

/**
 * @extends RequestFactory<AnotherRequestFactory>
 */
class AnotherRequestFactory extends RequestFactory
{
    protected string $request = SimpleRequest::class;

    public function definition(): array
    {
        return [];
    }
}
