<?php

namespace Magdonia\LaravelFactories\Request;

use Magdonia\LaravelFactories\RequestFactory;

trait HasFactory
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
