<?php

namespace Magdonia\LaravelFactories;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use JsonSerializable;

/**
 * @template TResourceFactory
 */
abstract class ResourceFactory
{
    protected ?string $wrapper = 'data';

    /** @var class-string<JsonResource> */
    protected string $resource;

    /** @var array<string, array<string, string>> */
    protected array $loaded = [];

    protected Request $request;

    protected Authenticatable $user;

    protected Model $model;

    protected Model|Collection|LengthAwarePaginator $resources;

    protected int $currentPage;

    protected int $from;

    protected int $to;

    protected int $total;

    protected int $lastPage;

    protected int $perPage;

    public function __construct()
    {
        $this->request = new Request();
    }

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
            ->prepend(config('laravel-factories.default-resource-factories-directory'))
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
            ->replace(config('laravel-factories.default-resource-factories-directory'), config('laravel-factories.default-resource-directory'))
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

        $this->request->setUserResolver(function () {
            return $this->user;
        });

        return $this;
    }

    public function make(): JsonResource
    {
        $class = $this->resource ?? self::resolveResource(static::class);

        if (isset($this->resources)) {
            if ($this->resources instanceof Model) {
                $response = new $class($this->resources);
            } else {
                $response = $class::collection($this->resources);
            }
        } else {
            $response = new $class();
        }

        return $response;
    }

    public function response(): TestResponse
    {
        $resource = $this->make();

        /** @phpstan-ignore-next-line  */
        return new TestResponse($resource->toResponse($this->request));
    }

    /**
     * @return array|Arrayable|JsonSerializable
     */
    /** @phpstan-ignore-next-line  */
    public function toArray()
    {
        return $this->make()->toArray($this->request);
    }

    /**
     * @return array<string|int, mixed>
     */
    public function json(): array
    {
        return $this->response()->json();
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
                                ->has($key, function (AssertableJson $json) {
                                    $this->assert($json);
                                });
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
                if ($this->wrapper) {
                    $json
                        ->has($this->wrapper, function (AssertableJson $json) {
                        });
                } else {
                    /** @phpstan-ignore-next-line  */
                    $this->resources->each(function ($item, $key) use ($json) {
                        $this->model = $item;
                        $json
                            ->has($key, function (AssertableJson $json) {
                                $this->assert($json);
                            });
                    });
                }
            };
        }

        $this->model = $this->resources;

        return function (AssertableJson $json) {
            if ($this->wrapper) {
                $json
                    ->has('data', function (AssertableJson $json) {
                        $this->assert($json);
                    });
            } else {
                $this->assert($json);
            }
        };
    }

    abstract public function definition(AssertableJson $json): void;

    /**
     * @param string $key
     * @param class-string<JsonResource> $resource
     * @param string|null $relation
     * @return $this
     */
    public function with(string $key, string $resource, ?string $relation = null): static
    {
        $this->loaded[$key] = ['resource' => $resource, 'relation' => $relation ?? $key];

        return $this;
    }

    /**
     * @param string|null $wrapper
     * @return $this
     */
    public function wrapper(?string $wrapper): static
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    protected function assert(AssertableJson $json): void
    {
        $this->definition($json);

        foreach ($this->loaded as $key => $definition) {
            $resource = $definition['resource'];
            $relation = $definition['relation'];

            if ($this->model->$relation instanceof Model) {
                $json->has($key, $resource::factory()->model($this->model->$relation)->wrapper(null)->create());
            } else {
                $json->has($key, $resource::factory()->collection($this->model->$relation)->wrapper(null)->create());
            }
        }
    }
}
