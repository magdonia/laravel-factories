<?php

namespace Magdonia\LaravelFactories\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Magdonia\LaravelFactories\Tests\Database\Factories\PostFactory;

class Post extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return new PostFactory();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
