<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Resources;

use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;
use Magdonia\LaravelFactories\Tests\Http\Resources\AnotherResource;

/**
 * @extends ResourceFactory<ForAnotherResourceFactory>
 * @property \Magdonia\LaravelFactories\Tests\Models\User $model
 */
class ForAnotherResourceFactory extends ResourceFactory
{
    protected string $resource = AnotherResource::class;

    public function definition(AssertableJson $json): void
    {
        $json
            ->where('auth', $this->user->username)
            ->where('username', $this->model->username);
    }
}
