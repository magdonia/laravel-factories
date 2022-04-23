<?php

namespace Magdonia\LaravelFactories\Concerns;

use Magdonia\LaravelFactories\RequestFactory;

trait HasRequestFactory
{
    public static function factory(): RequestFactory
    {
        /* @phpstan-ignore-next-line */
        return static::newFactory() ?? RequestFactory::new(static::class);
    }

    protected static function newFactory(): ?RequestFactory
    {
        return null;
    }
}
