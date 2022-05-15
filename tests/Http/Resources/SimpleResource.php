<?php

namespace Magdonia\LaravelFactories\Tests\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Magdonia\LaravelFactories\Concerns\HasResourceFactory;
use Magdonia\LaravelFactories\Tests\Models\User;

/**
 * @mixin User
 */
class SimpleResource extends JsonResource
{
    use HasResourceFactory;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'username' => $this->username,
        ];
    }
}
