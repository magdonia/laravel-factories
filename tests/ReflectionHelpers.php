<?php

namespace Magdonia\LaravelFactories\Tests;

use ReflectionObject;

trait ReflectionHelpers
{
    protected static function getPrivateProperty(object $obj, string $name): mixed
    {
        $class = new ReflectionObject($obj);
        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
