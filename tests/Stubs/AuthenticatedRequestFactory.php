<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Magdonia\LaravelFactories\RequestFactory;

/**
 * @extends RequestFactory<AuthenticatedRequestFactory>
 */
class AuthenticatedRequestFactory extends RequestFactory
{
    /**
     * @return $this<AuthenticatedRequestFactory>
     */
    public function withTitle(): self
    {
        $this->set('title', $this->faker->sentence());

        return $this;
    }
}
