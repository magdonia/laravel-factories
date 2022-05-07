<?php

namespace Magdonia\LaravelFactories;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;

/**
 * @template TResourceFactory
 */
abstract class ResourceFactory
{
    /** @var class-string<JsonResource> */
    protected string $resource;
    protected Authenticatable $user;
    protected Model $model;
    protected Model|Collection|LengthAwarePaginator $resources;
    protected int $currentPage;
    protected int $from;
    protected int $to;
    protected int $total;
    protected int $lastPage;
    protected int $perPage;

    /**
     * @param class-string<JsonResource> $resourceClass
     * @return ResourceFactory<TResourceFactory>
     */
    public static function new(string $resourceClass): ResourceFactory
    {
        $factoryClass = static::resolveFactory($resourceClass);

        return new $factoryClass();
    }

    /**
     * @param class-string<JsonResource> $requestClass
     * @return class-string<ResourceFactory>
     */
    public static function resolveFactory(string $requestClass): string
    {
        /** @phpstan-ignore-next-line  */
        return Str::of($requestClass)
            ->prepend(config('default-resource-factories-directory'))
            ->replace(config('laravel-factories.default-resource-directory'), '')
            ->append('Factory');
    }

    /**
     * @param class-string<ResourceFactory> $factoryClass
     * @return class-string<JsonResource>
     */
    public static function resolveResource(string $factoryClass): string
    {
        /** @phpstan-ignore-next-line  */
        return Str::of($factoryClass)
            ->replace(config('laravel-factories.default-resource-directory'), config('default-resource-factories-directory'))
            ->replace('Factory', '');
    }

    public function model(Model $model): static
    {
        $this->resources = $model;

        return $this;
    }

    public function collection(Collection $collection): static
    {
        $this->resources = $collection;

        return $this;
    }

    public function pagination(
        LengthAwarePaginator $collection,
        ?int $perPage = 15,
        ?int $currentPage = 1,
        ?int $from = 1,
        ?int $to = null,
        ?int $total = null,
        ?int $lastPage = null
    ): static {
        $this->resources = $collection;

        $this->perPage = $perPage ?? 15;
        $this->currentPage = $currentPage ?? 1;
        $this->from = $from ?? 1;
        $this->to = $to ?? count($collection->items());
        $this->total = $total ?? count($collection->items());
        $this->lastPage = $lastPage ?? (int) ceil($this->total / $this->perPage);

        return $this;
    }

    public function user(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function response(): array
    {
        $class = $this->resource ?? self::resolveResource(static::class);

        if (isset($this->resources)) {
            if ($this->resources instanceof Model) {
                /** @var JsonResource $resource */
                $resource = new $class($this->resources);
            } else {
                /** @var JsonResource $resource */
                $resource = $class::collection($this->resources);
            }
        }

        $request = new Request();

        if (isset($this->user)) {
            $request->setUserResolver(function () {
                return $this->user;
            });
        }

        /** @phpstan-ignore-next-line  */
        return (new TestResponse($resource->toResponse($request)))->json();
    }

    public function create(): Closure
    {
        if ($this->resources instanceof LengthAwarePaginator) {
            return function (AssertableJson $json) {
                $json
                    ->has('data', function (AssertableJson $json) {
                        /** @phpstan-ignore-next-line  */
                        $this->resources->each(function ($item, $key) use ($json) {
                            $this->model = $item;
                            $json
                                ->has($key, $this->definition());
                        });
                    })
                    ->has('meta', function (AssertableJson $json) {
                        $json
                            ->where('current_page', $this->currentPage)
                            ->where('from', $this->from)
                            ->where('to', $this->to)
                            ->where('total', $this->total)
                            ->where('last_page', $this->lastPage)
                            ->has('links')
                            ->has('path')
                            ->where('per_page', $this->perPage);
                    })
                    ->has('links');
            };
        }

        if ($this->resources instanceof Collection) {
            return function (AssertableJson $json) {
                $json
                    ->has('data', function (AssertableJson $json) {
                        /** @phpstan-ignore-next-line  */
                        $this->resources->each(function ($item, $key) use ($json) {
                            $this->model = $item;
                            $json
                                ->has($key, $this->definition());
                        });
                    });
            };
        }

        $this->model = $this->resources;

        return function (AssertableJson $json) {
            $json
                ->has('data', $this->definition());
        };
    }

    abstract public function definition(): Closure;
}
