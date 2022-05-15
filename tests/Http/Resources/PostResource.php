<?php

namespace Magdonia\LaravelFactories\Tests\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Magdonia\LaravelFactories\Concerns\HasResourceFactory;

class PostResource extends JsonResource
{
    use HasResourceFactory;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
