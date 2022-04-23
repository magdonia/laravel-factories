<?php

namespace Magdonia\LaravelFactories\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Magdonia\LaravelFactories\RequestFactory;
use Magdonia\LaravelFactories\Tests\Stubs\AnotherRequestFactory;
use Magdonia\LaravelFactories\Tests\Stubs\AuthenticatedRequest;
use Magdonia\LaravelFactories\Tests\Stubs\NewRequest;
use Magdonia\LaravelFactories\Tests\Stubs\SimpleRequest;
use Magdonia\LaravelFactories\Tests\Stubs\SimpleRequestFactory;
use Orchestra\Testbench\TestCase;

class FactoryTest extends TestCase
{
    use ReflectionHelpers;
    use WithFaker;

    public function test_it_should_return_new_factory_class_for_request(): void
    {
        $factory = SimpleRequest::factory()->create();

        $this->assertInstanceOf(SimpleRequestFactory::class, $factory);
    }

    public function test_it_should_return_new_factory_class_for_request_from_new_factory_method(): void
    {
        $factory = NewRequest::factory()->create();

        $this->assertInstanceOf(AnotherRequestFactory::class, $factory);
    }

    public function test_it_should_resolve_factory_class_name_for_given_request_class(): void
    {
        $requestClass = config('laravel-factories.default-request-directory') . 'AnyRequest';
        /* @phpstan-ignore-next-line */
        $factoryClass = RequestFactory::resolveFactory($requestClass);

        $this->assertEquals(
            config('laravel-factories.default-request-factories-directory') . 'AnyRequestFactory',
            $factoryClass
        );

        $requestClass = config('laravel-factories.default-request-directory') . 'Any\SubRequest';
        /* @phpstan-ignore-next-line */
        $factoryClass = RequestFactory::resolveFactory($requestClass);

        $this->assertEquals(
            config('laravel-factories.default-request-factories-directory') . 'Any\\SubRequestFactory',
            $factoryClass
        );
    }

    public function test_it_should_return_definition_as_seed(): void
    {
        $form = SimpleRequest::factory()->form();

        $this->assertEquals((new SimpleRequestFactory())->definition(), $form);
    }

    public function test_it_can_use_factory_to_generate_definition(): void
    {
        $form1 = SimpleRequest::factory()->withRandom()->form();
        $form2 = SimpleRequest::factory()->withRandom()->form();

        $this->assertArrayHasKey('unique_random', $form1);
        $this->assertArrayHasKey('unique_random', $form2);
        $this->assertNotEquals($form1['unique_random'], $form2['unique_random']);
    }

    public function test_it_should_set_given_key_value(): void
    {
        $key = $this->faker->word();
        $value = $this->faker->sentence();

        $form = SimpleRequest::factory()->set($key, $value)->form();

        $this->assertArrayHasKey($key, $form);
        $this->assertEquals($form[$key], $value);
    }

    public function test_it_can_have_states(): void
    {
        $value = $this->faker->sentence();

        $form = SimpleRequest::factory()->someState($value)->form();

        $this->assertArrayHasKey('state_key', $form);
        $this->assertEquals($form['state_key'], $value);
    }

    public function test_it_can_unset_an_input(): void
    {
        $request = SimpleRequest::factory();

        $this->assertArrayHasKey('title', $request->form());

        $this->assertArrayNotHasKey('title', $request->unset('title')->form());
    }

    public function test_it_should_validate_request(): void
    {
        SimpleRequest::factory()->validate()
            ->assertJsonMissingValidationErrors('title');

        SimpleRequest::factory()->withoutTitle()->validate()
            ->assertJsonValidationErrors('title');
    }

    public function test_it_should_make_a_request(): void
    {
        /** @var SimpleRequest $request */
        $request = SimpleRequest::factory()->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertSame($this->app, $this->getPrivateProperty($request, 'container'));
        $this->assertSame($this->app->make(Redirector::class), $this->getPrivateProperty($request, 'redirector'));
        $this->assertEquals(null, $request->user());
        $this->assertInstanceOf(\Illuminate\Routing\Route::class, $request->route());
        $this->assertEquals('/', $request->route()->uri); /** @phpstan-ignore-line */
        $this->assertEquals(['GET', 'HEAD'], $request->route()->methods); /** @phpstan-ignore-line */
        $this->assertEquals([], $request->route()->parameters); /** @phpstan-ignore-line */
    }

    public function test_it_should_make_a_request_with_given_inputs(): void
    {
        $key = $this->faker->word();
        $value = $this->faker->sentence();
        $request = SimpleRequest::factory()->make([$key => $value]);

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertSame($this->app, $this->getPrivateProperty($request, 'container'));
        $this->assertSame($this->app->make(Redirector::class), $this->getPrivateProperty($request, 'redirector'));
        $this->assertEquals(null, $request->user());
        $this->assertEquals('/', $request->route()->uri); /** @phpstan-ignore-line */
        $this->assertEquals(['GET', 'HEAD'], $request->route()->methods); /** @phpstan-ignore-line */
        $this->assertEquals([], $request->route()->parameters); /** @phpstan-ignore-line */
        $this->assertArrayHasKey($key, $request->all());
        $this->assertEquals($value, $request->input($key));
    }

    public function test_it_can_make_as_guest(): void
    {
        $request = SimpleRequest::factory()->asGuest()->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertNull($request->user());
    }

    public function test_it_can_make_as_given_user(): void
    {
        $user = new User();
        $request = SimpleRequest::factory()->as($user)->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertEquals($user, $request->user());
    }

    public function test_it_can_set_method(): void
    {
        $method = $this->faker->randomElement(['GET', 'POST', 'PATCH', 'DELETE']);
        $request = SimpleRequest::factory()->method($method)->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertContains($method, $request->route()->methods); /** @phpstan-ignore-line */
    }

    public function test_it_can_set_route_params(): void
    {
        $key = $this->faker->word();
        $value = $this->faker->randomElement([null, $this->faker->randomDigit(), $this->faker->word()]);
        $request = SimpleRequest::factory()->routeParam($key, $value)->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertEquals([$key => $value], $request->route()->parameters); /** @phpstan-ignore-line */
    }

    public function test_it_can_set_route(): void
    {
        Route::name('welcome')->get('/welcome', function () {
        });

        $request = SimpleRequest::factory()->route('welcome')->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertEquals(route('welcome'), $request->route()->uri); /** @phpstan-ignore-line */
    }

    public function test_it_should_resolve_request_class_name_for_given_factory_class_name(): void
    {
        $requestClass = config('laravel-factories.default-request-factories-directory') . 'AnyRequestFactory';
        $factoryClass = RequestFactory::resolveRequest($requestClass); /** @phpstan-ignore-line */

        $this->assertEquals(
            config('laravel-factories.default-request-directory') . 'AnyRequest',
            $factoryClass
        );

        $requestClass = config('laravel-factories.default-request-factories-directory') . 'Any\\SubRequestFactory';
        $factoryClass = RequestFactory::resolveRequest($requestClass); /** @phpstan-ignore-line */

        $this->assertEquals(
            config('laravel-factories.default-request-directory') . 'Any\SubRequest',
            $factoryClass
        );
    }

    public function test_it_should_make_from_given_request_class(): void
    {
        $request = NewRequest::factory()->make();

        $this->assertInstanceOf(SimpleRequest::class, $request);
    }

    public function test_factory_should_call_configure_method_on_constructor(): void
    {
        $this->expectExceptionMessage('Should throw an error');

        new class () extends RequestFactory {
            protected function configure(): void
            {
                throw new \Exception('Should throw an error');
            }
        };
    }

    public function test_it_should_return_empty_array_as_definition(): void
    {
        $this->assertEquals(
            [],
            (new class () extends RequestFactory {})->form()
        );
    }

    public function test_it_should_return_given_attributes_as_definition(): void
    {
        $key1 = $this->faker->word();
        $key2 = $this->faker->randomDigit();
        $value1 = $this->faker->word();
        $value2 = null;

        $form = [
            $key1 => $value1,
            $key2 => $value2,
        ];

        $this->assertEquals(
            $form,
            (new class () extends RequestFactory {})->form($form)
        );
    }

    public function test_states_should_set_given_states(): void
    {
        $key1 = $this->faker->word();
        $key2 = $this->faker->randomDigit();
        $value1 = $this->faker->word();
        $value2 = null;

        $states = [
            $key1 => $value1,
            $key2 => $value2,
        ];

        $this->assertEquals(
            $states,
            (new class () extends RequestFactory {})->states($states)->form()
        );
    }

    public function test_it_should_resolve_user_for_validate(): void
    {
        $response = AuthenticatedRequest::factory()->asGuest()->validate();
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $user = new User();
        /* @phpstan-ignore-next-line */
        $user->is_admin = false;
        $response = AuthenticatedRequest::factory()->as($user)->validate();
        $response->assertForbidden();

        $user = new User();
        /* @phpstan-ignore-next-line */
        $user->is_admin = true;
        $response = AuthenticatedRequest::factory()->as($user)->validate();
        $response->assertJsonValidationErrors('title');

        $response = AuthenticatedRequest::factory()->withTitle()->as($user)->validate();
        $response->assertSuccessful();
    }

    public function test_validate_can_set_attributes(): void
    {
        SimpleRequest::factory()->withoutTitle()->validate()
            ->assertJsonValidationErrors('title');
        SimpleRequest::factory()->withoutTitle()->validate(['title' => $this->faker->word()])
            ->assertJsonMissingValidationErrors('title');
    }
}
