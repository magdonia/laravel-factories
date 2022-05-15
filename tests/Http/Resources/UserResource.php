<?php

namespace Magdonia\LaravelFactories\Tests\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Magdonia\LaravelFactories\Concerns\HasResourceFactory;
use Magdonia\LaravelFactories\Tests\Models\User;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    use HasResourceFactory;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}
