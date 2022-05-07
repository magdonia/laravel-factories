<?php

namespace Magdonia\LaravelFactories\Concerns;

use Magdonia\LaravelFactories\ResourceFactory;

trait HasResourceFactory
{
    public static function factory(): ResourceFactory
    {
        /* @phpstan-ignore-next-line */
        return static::newFactory() ?? ResourceFactory::new(static::class);
    }

    protected static function newFactory(): ?ResourceFactory
    {
        return null;
    }
}
