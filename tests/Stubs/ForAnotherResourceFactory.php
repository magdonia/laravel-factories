<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Closure;
use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;

/**
 * @extends ResourceFactory<ForAnotherResourceFactory>
 * @property User $model
 */
class ForAnotherResourceFactory extends ResourceFactory
{
    protected string $resource = AnotherResource::class;

    public function definition(): Closure
    {
        return function (AssertableJson $json) {
            $json
                ->where('auth', $this->user->username)
                ->where('username', $this->model->username);
        };
    }
}
