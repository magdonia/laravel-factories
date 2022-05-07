<?php

namespace Magdonia\LaravelFactories\Tests\Stubs;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Magdonia\LaravelFactories\Concerns\HasResourceFactory;
use Magdonia\LaravelFactories\ResourceFactory;

/**
 * @mixin User
 */
class AnotherResource extends JsonResource
{
    use HasResourceFactory;

    protected static function newFactory(): ?ResourceFactory
    {
        return new ForAnotherResourceFactory();
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'auth' => $request->user()->username,
            'username' => $this->username,
        ];
    }
}
