<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Closure;
use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;

/**
 * @extends ResourceFactory<SimpleResourceFactory>
 * @property User $model
 */
class SimpleResourceFactory extends ResourceFactory
{
    public function definition(): Closure
    {
        return function (AssertableJson $json) {
            $json->where('username', $this->model->username);
        };
    }
}
