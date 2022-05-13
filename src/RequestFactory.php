<?php

namespace Magdonia\LaravelFactories;

use Closure;
use Exception;
use Faker\Generator;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

/**
 * @template TRequestFactory
 */
abstract class RequestFactory
{
    /**
     * @var class-string<Request> string
     */
    protected string $request;

    protected Generator $faker;

    protected string $uri = '/';

    protected string $method = 'GET';

    /**
     * @var array<string|int, mixed>
     */
    protected array $parameters = [];

    /**
     * @var array<int, int|string>
     */
    protected array $unset = [];

    /**
     * @var array<string|int, mixed>
     */
    protected array $cookies = [];

    /**
     * @var array<string|int, mixed>
     */
    protected array $files = [];

    /**
     * @var array<string|int, mixed>
     */
    protected array $server = ['HTTP_ACCEPT' => 'application/json'];

    /** @var string|resource|null */
    protected mixed $content = null;

    protected Closure $userResolver;

    /**
     * @var array<string|int, mixed>
     */
    protected array $routeParameters = [];

    public function __construct()
    {
        $this->faker = $this->withFaker();
        $this->configure();
    }

    protected function configure(): void
    {
        //
    }

    /**
     * @return array<string|int, mixed>
     */
    protected function definition(): array
    {
        return [];
    }

    /**
     * @param class-string<Request> $requestClass
     * @return RequestFactory<TRequestFactory>
     */
    public static function new(string $requestClass): RequestFactory
    {
        $factoryClass = static::resolveFactory($requestClass);

        return new $factoryClass();
    }

    /**
     * @param class-string<Request> $requestClass
     * @return class-string<TRequestFactory>
     */
    public static function resolveFactory(string $requestClass): string
    {
        /** @phpstan-ignore-next-line  */
        return Str::of($requestClass)
            ->prepend(config('default-request-factories-directory'))
            ->replace(config('laravel-factories.default-request-directory'), '')
            ->append('Factory');
    }

    /**
     * @param class-string<TRequestFactory> $factoryClass
     * @return class-string<Request>
     */
    public static function resolveRequest(string $factoryClass): string
    {
        /** @phpstan-ignore-next-line  */
        return Str::of($factoryClass)
            ->replace(config('laravel-factories.default-request-directory'), config('default-request-factories-directory'))
            ->replace('Factory', '');
    }

    /**
     * @param array<string|int, mixed>|null $attributes
     * @return RequestFactory<TRequestFactory>
     */
    private function setup(?array $attributes = []): RequestFactory
    {
        $this->state($attributes);
        $this->parameters = $this->definition() + $this->parameters;

        if (count($this->unset)) {
            foreach ($this->unset as $key) {
                unset($this->parameters[$key]);
            }
        }

        return $this;
    }

    /**
     * @param array<string|int, mixed>|null $attributes
     * @return RequestFactory<TRequestFactory>
     */
    public function create(?array $attributes = []): RequestFactory
    {
        return $this->setup($attributes);
    }

    /**
     * @param array<string|int, mixed>|null $attributes
     * @return array<string|int, mixed>
     */
    public function form(?array $attributes = []): array
    {
        return $this->create($attributes)->parameters;
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @return RequestFactory<TRequestFactory>
     */
    public function set(string|int $key, mixed $value): RequestFactory
    {
        $this->parameters[$key] = $value;
        if (($key = array_search($key, $this->unset)) !== false) {
            unset($this->unset[$key]);
        }

        return $this;
    }

    /**
     * @param string|array<int, int|string> $keys
     * @return RequestFactory<TRequestFactory>
     */
    public function unset(string|array $keys): RequestFactory
    {
        if (is_string($keys)) {
            $keys = func_get_args();
        }

        $this->unset = array_merge($this->unset, $keys);

        return $this;
    }

    /**
     * @param array<string|int, mixed> $attributes
     * @return RequestFactory<TRequestFactory>
     */
    public function state(?array $attributes = []): RequestFactory
    {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param array<string|int, mixed> $attributes
     * @return FormRequest
     */
    public function make(?array $attributes = []): FormRequest
    {
        $this->setup($attributes);
        $requestClassName = $this->request ?? self::resolveRequest(static::class); /* @phpstan-ignore-line */
        /** @var FormRequest $request */
        $request = $requestClassName::createFromBase(Request::create($this->uri, $this->method, $this->parameters, $this->cookies, $this->files, $this->server, $this->content));
        $request->setContainer(app())->setRedirector(app()->make(Redirector::class));

        if (isset($this->userResolver)) {
            $request->setUserResolver($this->userResolver);
        }

        $route = new Route($this->method, $this->uri, fn () => null);
        $route->parameters = $this->routeParameters;
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    /**
     * @param array<string|int, mixed> $attributes
     * @return TestResponse
     */
    public function validate(?array $attributes = []): TestResponse
    {
        $request = $this->make($attributes);

        try {
            $request->validateResolved();
            $response = new Response();
        } catch (Exception $exception) {
            $response = app()->make(Handler::class)->render($request, $exception);
        }

        return TestResponse::fromBaseResponse($response);
    }

    protected function withFaker(): Generator
    {
        return app()->make(Generator::class);
    }

    /**
     * @return RequestFactory<TRequestFactory>
     */
    public function asGuest(): RequestFactory
    {
        $this->userResolver = function () {
        };

        return $this;
    }

    /**
     * @param User $user
     * @return RequestFactory<TRequestFactory>
     */
    public function as(User $user): RequestFactory
    {
        $this->userResolver = fn () => $user;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return RequestFactory<TRequestFactory>
     */
    public function routeParam(string $key, mixed $value): RequestFactory
    {
        $this->routeParameters[$key] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return RequestFactory<TRequestFactory>
     */
    public function route(string $name): RequestFactory
    {
        $this->uri = route($name);

        return $this;
    }

    /**
     * @param string $method
     * @return RequestFactory<TRequestFactory>
     */
    public function method(string $method): RequestFactory
    {
        $this->method = $method;

        return $this;
    }
}
