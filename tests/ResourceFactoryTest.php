<?php

namespace Magdonia\LaravelFactories\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\AssertableJsonString;
use Illuminate\Testing\Fluent\AssertableJson;
use Magdonia\LaravelFactories\ResourceFactory;
use Magdonia\LaravelFactories\Tests\Stubs\AnotherResource;
use Magdonia\LaravelFactories\Tests\Stubs\ForAnotherResourceFactory;
use Magdonia\LaravelFactories\Tests\Stubs\SimpleResource;
use Magdonia\LaravelFactories\Tests\Stubs\SimpleResourceFactory;
use Magdonia\LaravelFactories\Tests\Stubs\User;
use Orchestra\Testbench\TestCase;

class ResourceFactoryTest extends TestCase
{
    use ReflectionHelpers;
    use WithFaker;

    public function test_it_should_return_factory_class_for_resource(): void
    {
        $factory = SimpleResource::factory();

        $this->assertInstanceOf(SimpleResourceFactory::class, $factory);
    }

    public function test_it_should_resolve_factory_class_name_for_given_resource_class(): void
    {
        Config::set('laravel-factories.default-resource-factories-directory', 'Factories\\Resources\\');
        Config::set('laravel-factories.default-resource-directory', 'App\\Resources\\');

        $resourceClass = config('laravel-factories.default-resource-directory') . 'AnyResource';
        /* @phpstan-ignore-next-line */
        $factoryClass = ResourceFactory::resolveFactory($resourceClass);

        $this->assertEquals(
            config('laravel-factories.default-resource-factories-directory') . 'AnyResourceFactory',
            $factoryClass
        );

        $resourceClass = config('laravel-factories.default-resource-directory') . 'Any\SubResource';
        /* @phpstan-ignore-next-line */
        $factoryClass = ResourceFactory::resolveFactory($resourceClass);

        $this->assertEquals(
            config('laravel-factories.default-resource-factories-directory') . 'Any\\SubResourceFactory',
            $factoryClass
        );
    }

    public function test_it_should_resolve_resource_class_name_for_given_factory_class_name(): void
    {
        Config::set('laravel-factories.default-resource-factories-directory', 'Factories\\Resources\\');
        Config::set('laravel-factories.default-resource-directory', 'App\\Resources\\');

        $resourceClass = config('laravel-factories.default-resource-factories-directory') . 'AnyResourceFactory';
        $factoryClass = ResourceFactory::resolveResource($resourceClass); /** @phpstan-ignore-line */

        $this->assertEquals(
            config('laravel-factories.default-resource-directory') . 'AnyResource',
            $factoryClass
        );

        $resourceClass = config('laravel-factories.default-resource-factories-directory') . 'Any\\SubResourceFactory';
        $factoryClass = ResourceFactory::resolveResource($resourceClass); /** @phpstan-ignore-line */

        $this->assertEquals(
            config('laravel-factories.default-resource-directory') . 'Any\SubResource',
            $factoryClass
        );
    }

    public function test_it_return_factory_from_new_factory_method(): void
    {
        $factory = AnotherResource::factory();

        $this->assertInstanceOf(ForAnotherResourceFactory::class, $factory);
    }

    public function test_it_should_set_model(): void
    {
        $model = new User();
        $factory = SimpleResource::factory();
        $factory->model($model);

        $this->assertEquals($model, $this->getPrivateProperty($factory, 'resources'));
    }

    public function test_it_should_set_collection(): void
    {
        $model1 = new User();
        $model2 = new User();
        $collection = collect([$model1, $model2]);
        $factory = SimpleResource::factory();
        $factory->collection(collect([$model1, $model2]));

        $this->assertEquals($collection, $this->getPrivateProperty($factory, 'resources'));
    }

    public function test_it_should_set_length_aware_pagination(): void
    {
        $username1 = $this->faker->userName();
        $username2 = $this->faker->userName();
        $model1 = new User();
        $model1->username = $username1;
        $model2 = new User();
        $model2->username = $username2;
        $pagination = new LengthAwarePaginator(collect([$model1, $model2]), 2, 15);
        $factory = SimpleResource::factory();
        $factory->pagination($pagination);

        $this->assertEquals($pagination, $this->getPrivateProperty($factory, 'resources'));
    }

    public function test_it_should_return_resource_json(): void
    {
        $username = $this->faker->userName();
        $model = new User();
        $model->username = $username;

        $response = SimpleResource::factory()->model($model)->response();
        $this->assertEquals((new SimpleResourceFactory())->model($model)->response(), $response);
    }

    public function test_json_response(): void
    {
        $username = $this->faker->userName();
        $model = new User();
        $model->username = $username;

        $this->assertEquals(['data' => ['username' => $username]], SimpleResource::factory()->model($model)->response());
    }

    public function test_json_response_with_given_authenticated_user(): void
    {
        $username1 = $this->faker->userName();
        $auth = new \Illuminate\Foundation\Auth\User();
        $auth->username = $username1;

        $username2 = $this->faker->userName();
        $model = new User();
        $model->username = $username2;

        $this->assertEquals(['data' => ['auth' => $username1, 'username' => $username2]], AnotherResource::factory()->user($auth)->model($model)->response());
    }

    public function test_json_with_collection(): void
    {
        $username1 = $this->faker->userName();
        $username2 = $this->faker->userName();
        $model1 = new User();
        $model1->username = $username1;
        $model2 = new User();
        $model2->username = $username2;

        $collection = collect([$model1, $model2]);

        $this->assertEquals([
            'data' => [
                [
                    'username' => $username1,
                ],
                [
                    'username' => $username2,
                ],
            ],
        ], SimpleResource::factory()->collection($collection)->response());
    }

    public function test_pagination_response(): void
    {
        $username1 = $this->faker->userName();
        $username2 = $this->faker->userName();
        $model1 = new User();
        $model1->username = $username1;
        $model2 = new User();
        $model2->username = $username2;

        $pagination = new LengthAwarePaginator(collect([$model1, $model2]), 2, 15);

        $this->assertEquals([
            'data' => [
                [
                    'username' => $username1,
                ],
                [
                    'username' => $username2,
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'to' => 2,
                'total' => 2,
                'last_page' => 1,
                'links' => [
                    [
                        'url' => null,
                        'label' => '&laquo; Previous',
                        'active' => false,
                    ],
                    [
                        'url' => '/?page=1',
                        'label' => '1',
                        'active' => true,
                    ],
                    [
                        'url' => null,
                        'label' => 'Next &raquo;',
                        'active' => false,
                    ],
                ],
                'path' => '/',
                'per_page' => 15,
            ],
            'links' => [
                'first' => '/?page=1',
                'last' => '/?page=1',
                'prev' => null,
                'next' => null,
            ],
        ], SimpleResource::factory()->pagination($pagination)->response());
    }

    public function test_it_should_create_an_assertable_json_for_single_resource(): void
    {
        $username = $this->faker->userName();
        $model = new User();
        $model->username = $username;

        $assertableJson = SimpleResource::factory()->model($model)->create();

        $assert = AssertableJson::fromAssertableJsonString(new AssertableJsonString([
            'data' => [
                'username' => $username,
            ],
        ]));

        $assertableJson($assert);

        $assert->interacted();
    }

    public function test_it_should_create_an_assertable_json_for_collection_resource(): void
    {
        $username1 = $this->faker->userName();
        $username2 = $this->faker->userName();
        $model1 = new User();
        $model1->username = $username1;
        $model2 = new User();
        $model2->username = $username2;

        $assertableJson = SimpleResource::factory()->collection(collect([$model1, $model2]))->create();

        $assert = AssertableJson::fromAssertableJsonString(new AssertableJsonString([
            'data' => [
                ['username' => $username1],
                ['username' => $username2],
            ],
        ]));

        $assertableJson($assert);

        $assert->interacted();
    }

    public function test_it_should_create_an_assertable_json_for_pagination_resource(): void
    {
        $username1 = $this->faker->userName();
        $username2 = $this->faker->userName();
        $model1 = new User();
        $model1->username = $username1;
        $model2 = new User();
        $model2->username = $username2;

        $pagination = new LengthAwarePaginator(collect([$model1, $model2]), 2, 15);

        $assertableJson = SimpleResource::factory()->pagination($pagination)->create();

        $assert = AssertableJson::fromAssertableJsonString(new AssertableJsonString([
            'data' => [
                ['username' => $username1],
                ['username' => $username2],
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'to' => 2,
                'total' => 2,
                'last_page' => 1,
                'links' => [
                    [
                        'url' => null,
                        'label' => '&laquo; Previous',
                        'active' => false,
                    ],
                    [
                        'url' => '/?page=1',
                        'label' => '1',
                        'active' => true,
                    ],
                    [
                        'url' => null,
                        'label' => 'Next &raquo;',
                        'active' => false,
                    ],
                ],
                'path' => '/',
                'per_page' => 15,
            ],
            'links' => [
                'first' => '/?page=1',
                'last' => '/?page=1',
                'prev' => null,
                'next' => null,
            ],
        ]));

        $assertableJson($assert);

        $assert->interacted();
    }

    public function test_it_should_have_access_to_authenticated_user(): void
    {
        $username1 = $this->faker->userName();
        $auth = new \Illuminate\Foundation\Auth\User();
        $auth->username = $username1;

        $username2 = $this->faker->userName();
        $model = new User();
        $model->username = $username2;

        $assertableJson = AnotherResource::factory()->user($auth)->model($model)->create();

        $assert = AssertableJson::fromAssertableJsonString(new AssertableJsonString([
            'data' => [
                'auth' => $username1,
                'username' => $username2,
            ],
        ]));

        $assertableJson($assert);

        $assert->interacted();
    }
}
