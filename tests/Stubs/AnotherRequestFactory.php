<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Magdonia\LaravelFactories\RequestFactory;

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
