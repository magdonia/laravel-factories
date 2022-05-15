<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Resources;

use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;
use Magdonia\LaravelFactories\Tests\Models\User;

/**
 * @extends ResourceFactory<UserResourceFactory>
 * @property User $model
 */
class UserResourceFactory extends ResourceFactory
{
    public function definition(AssertableJson $json): void
    {
        $json
            ->where('id', $this->model->id)
            ->where('name', $this->model->name)
            ->where('email', $this->model->email);
    }
}
