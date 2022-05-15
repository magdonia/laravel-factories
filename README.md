# Use Laravel Factories package to write your tests easier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/magdonia/laravel-factories.svg?style=flat-square)](https://packagist.org/packages/magdonia/laravel-factories)
[![Total Downloads](https://img.shields.io/packagist/dt/magdonia/laravel-factories?style=flat-square)](https://packagist.org/packages/magdonia/laravel-factories)
![GitHub Actions](https://github.com/magdonia/laravel-factories/actions/workflows/run-tests.yml/badge.svg)

This is a package to make it easy to write tests for Laravel application.

## Example usages

### Request Factories
Feature tests:
```php
// before
public function test_user_can_create_a_post(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $genre = Genre::factory()->create();
    $form = [
        'title' => $this->faker->sentence(),
        'description' => $this->faker->randomElement([null, $this->faker->sentence()]),
        'published' => $this->faker->boolean(),
        'image' => $this->faker->image(),
        'category_id' => Category::factory()->for($user)->create()->id,
    ];
    $response = $this->postJson(route('post.store', ['genre' => $genre]), $form);
    $response->assertCreated();
    $this->assertEquals($form['title'], $user->posts->first()->title);
}

// after
public function test_user_can_create_a_post(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $genre = Genre::factory()->create();
    $form = StorePostRequest::factory()->form();
    $response = $this->postJson(route('post.store', ['genre' => $genre]), $form);
    $response->assertCreated();
    $this->assertEquals($form['title'], $user->posts->first()->title);
}
```

Validations tests:
```php
// before
public function test_title_is_required_string(): void
{
    $genre = Genre::factory()->create();

    $title = null;
    $response = $this->postJson(route('post.store', ['genre' => $genre]), ['title' => $title])
    $response->assertJsonValidationErrors('title');
    
    $title = $this->faker->randomDigit();
    $response = $this->postJson(route('post.store', ['genre' => $genre]), ['title' => $title])
    $response->assertJsonValidationErrors('title');
    
    $title = $this->faker->boolean();
    $response = $this->postJson(route('post.store', ['genre' => $genre]), ['title' => $title])
    $response->assertJsonValidationErrors('title');
    
    $title = $this->faker->sentence();
    $response = $this->postJson(route('post.store', ['genre' => $genre]), ['title' => $title])
    $response->assertJsonMissingValidationErrors('title');
}

// after
public function test_title_is_required_string(): void
{
    $response = StorePostRequest::factory()->invalidTitle()->validate();
    $response->assertJsonValidationErrors('title');
    
    $response = StorePostRequest::factory()->validate();
    $response->assertJsonMissingValidationErrors('title');
    
    $response = StorePostRequest::factory()->unset('title')->validate();
    $response->assertJsonValidationErrors('title');
}
```

Validation tests when authenticated user needed

```php
// before
public function test_category_id_should_belong_to_user(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $genre = Genre::factory()->create();

    $category = Category::factory()->create();
    $response = $this->postJson(route('post.store', ['genre' = >$genre]), ['category_id' => $category->id])
    $response->assertJsonValidationErrors('category_id');
    
    $category = Category::factory()->for($user)->create();
    $response = $this->postJson(route('post.store', ['genre' = >$genre]), ['category_id' => $category->id])
    $response->assertJsonMissingValidationErrors('category_id');
}

// after
public function test_category_id_should_belong_to_user(): void
{
    $response = StorePostRequest::factory()->as(User::factory()->create())->validate();
    $response->assertJsonMissingValidationErrors('category_id');
    
    $request = StorePostRequest::factory();
    $response = $request->validate();
    $response->assertJsonMissingValidationErrors('category_id');
    
    // You have access to user or category on the request
    $user = $request->user;
    $category = $request->category;
}
```

Test Unauthorized condition test

```php
// before
public function test_guest_can_not_create_post(): void
{
    $genre = Genre::factory()->create();
    $response = $this->postJson(route('post.store', ['genre' => $genre]), $form)->assertUnauthorized();
}

// after
public function test_guest_can_not_create_post(): void
{
    StorePostRequest::factory()->asGuest()->validate()->assertUnauthorized();    
}
```

Validation test when route params are required

```php
// before
public function test_description_can_not_be_empty_when_genre_is_programming(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $genre = Genre::factory()->programming()->create();
    $form = [
        'description' => null,
    ];
    $response = $this->postJson(route('post.store', ['genre' => $genre]), $form);
    $response->assertJsonValidationErrors('description');
    
    $form = [
        'description' => $this->faker->sentence(),
    ];
    $response = $this->postJson(route('post.store', ['genre' => $genre]), $form);
    $response->assertJsonMissingValidationErrors('description');
}

// after
public function test_description_can_not_be_empty_when_genre_is_programming(): void
{
    $genre = Genre::factory()->programming()->create();

    $response = StorePostRequest::factory()->routeParam('genre', $genre->id)->validate(['description' => null]);
    $response->assertJsonValidationErrors('description');
    
    $response = StorePostRequest::factory()->routeParam('genre', $genre->id)->validate(['description' => $this->faker->sentence()]);
    $response->assertJsonMissingValidationErrors('description');
}
```

Validation test when there is a check on specific route

```php
// before
public function test_user_id_is_required_when_request_comes_from_api(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $form = [
        'user_id' => null,
    ];
    $response = $this->postJson('api.post.store', $form);
    $response->assertJsonValidationErrors('user_id');
    
    $form = [
        'user_id' => User::factory()->create()->id,
    ];
    $response = $this->postJson('api.post.store', $form);
    $response->assertJsonMissingValidationErrors('user_id');
}

// after
public function test_user_id_is_required_when_request_comes_from_api(): void
{
    $response = StorePostRequest::factory()->route('api.post.store')->validate(['user_id' => null]);
    $response->assertJsonValidationErrors('user_id');
    
    $response = StorePostRequest::factory()->route('api.post.store')->validate(['user_id' => User::factory()->create()->id]);
    $response->assertJsonMissingValidationErrors('user_id');
}
```

Validation test when there is a check on specific method

```php
// before
public function test_user_id_is_required_when_request_comes_as_get(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $form = [
        'user_id' => null,
    ];
    $response = $this->getJson('/api/post', $form);
    $response->assertJsonValidationErrors('user_id');
    
    $form = [
        'user_id' => User::factory()->create()->id,
    ];
    $response = $this->getJson('/api/post', $form);
    $response->assertJsonMissingValidationErrors('user_id');
}

// after
public function test_user_id_is_required_when_request_comes_as_get(): void
{
    $response = StorePostRequest::factory()->method('GET')->validate(['user_id' => null]);
    $response->assertJsonValidationErrors('user_id');
    
    $response = StorePostRequest::factory()->method('GET')->validate(['user_id' => User::factory()->create()->id]);
    $response->assertJsonMissingValidationErrors('user_id');
}
```

### Resource Factories
Feature tests:

Testing a single resource
```php
// before
public function test_it_should_show_a_post(): void
{
    $post = Post::factory()->create();
    $response = $this->getJson(route('post.show', $post));
    $response->assertJson(function (AssertableJson $json) use ($post) {
        $json
            ->has('data', function(AssertableJson $json) use ($post) {
                $json
                    ->where('title', $post->title)
                    ->where('description', $post->description)
                    ->where('image', $post->image)
                    ->where('created_at', $post->created_at->toJSON())
                    ->where('category', [
                        'id' => $post->category->id,
                        'title' => $post->category->title,
                    ]);
            });
    });
}

// after
public function test_it_should_show_a_post(): void
{
    $post = Post::factory()->create();
    $response = $this->getJson(route('post.show', $post));
    $response->assertJson(
        PostResource::factory()
            ->model($post->load('category'))
            ->with('category', CategoryResource::class)
            ->create()
    );
}
```

Testing a pagination
```php
// before
public function test_it_should_return_posts(): void
{
    $posts = Post::factory()->count(2)->create();
    $response = $this->getJson(route('post.index'));
    $response->assertJson(function (AssertableJson $json) use ($posts) {
        $json
            ->has('data', function (AssertableJson $json) {
                $json
                    ->has(0, function (AssertableJson $json) {
                        ->where('title', $posts->first()->title)
                        ->where('description', $posts->first()->description)
                        ->where('image', $posts->first()->image)
                        ->where('created_at', $posts->first()->created_at->toJSON())
                        ->where('category', [
                            'id' => $posts->first()->category->id,
                            'title' => $posts->first()->category->title,
                        ]);
                    })
                    ->has(1, function (AssertableJson $json) {
                        ->where('title', $posts->last()->title)
                        ->where('description', $posts->last()->description)
                        ->where('image', $posts->last()->image)
                        ->where('created_at', $posts->last()->created_at->toJSON())
                        ->where('category', [
                            'id' => $posts->last()->category->id,
                            'title' => $posts->last()->category->title,
                        ]);
                    })
            })
            ->has('meta', function (AssertableJson $json) {
                $json
                    ->where('current_page', 1)
                    ->where('from', 1)
                    ->where('to', 2)
                    ->where('total', 2)
                    ->etc();
            })
            ->etc();
    });
}

// after
public function test_it_should_show_a_post(): void
{
    $posts = Post::factory()->count(2)->create();
    $response = $this->getJson(route('post.index'));
    $response->assertJson(
        PostResource::factory()
            ->with('category', CategoryResource::class)
            ->pagination(
                collection: $posts->each->load('category'),
            )
            ->create()
    );
}
```
Getting actual response from resource class

Single resource
```php
public function test_it_should_return_single_resource_response(): void
{
    $post = Post::factory()->create();
    $this->assertEquals([
        'data' => [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
        ]
    ], PostResource::factory()->model($post)->json());
    
    // usage with response
    PostResource::factory()->model($post)->response()
        ->assertJsonStructure(['data' => [
            'id',
            'title',
            'description',
        ]]);
}
```
Collection resource
```php
public function test_it_should_return_collection_resource_response(): void
{
    $posts = Post::factory()->count(2)->create();
    $this->assertEquals([
        'data' => [
            [
                'id' => $posts->first()->id,
                'title' => $posts->first()->title,
                'description' => $posts->first()->description,
            ],
            [
                'id' => $posts->last()->id,
                'title' => $posts->last()->title,
                'description' => $posts->last()->description,
            ]
        ],
    ], PostResource::factory()->collection($posts)->json())
}
```
Pagination resource
```php
public function test_it_should_return_pagination_resource_response(): void
{
    $posts = Post::factory()->count(2)->create();
    $this->assertEquals([
        'data' => [
            [
                'id' => $posts->first()->id,
                'title' => $posts->first()->title,
                'description' => $posts->first()->description,
            ],
            [
                'id' => $posts->last()->id,
                'title' => $posts->last()->title,
                'description' => $posts->last()->description,
            ]
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
        ],
    ], PostResource::factory()->pagination(Post::paginate())->json())
}
```
## Installation

You can install the package via composer:

```bash
composer require magdonia/laravel-factories
```

## Usage
### Requests
Add the `HasRequestFactory` trait to your request classes:

```php
use Magdonia\LaravelFactories\Concerns\HasRequestFactory;

class YourRequest extends FormRequest
{
    use HasRequestFactory;
}
```

Add a factory class for your request in your request factories directory. 

```php
<?php

namespace Tests\RequestFactories;

use Magdonia\LaravelFactories\RequestFactory;

class AnotherRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            // Your definition goes here
        ];
    }
}
```
How to use it?
```php
// Get a factory object
$factory = PostStoreRequest::factory()->create();

// Set attributes on factory
$factory = PostStoreRequest::factory()->create(['title' => $title])

// Get a form from request factory
$form = PostStoreRequest::factory()->form();

// Get a form from request factory and override or add additional fields
$form = PostStoreRequest::factory()->form(['title' => $title, 'added' => 'something']);

// Add states for request form
$form = PostStoreRequest::factory()->category($category)->form();

// Set values on request form
$form = PostStoreRequest::factory()->set($key, $value)->form();

// Unset values on request form
$form = PostStoreRequest::factory()->unset($key)->form();
$form = PostStoreRequest::factory()->unset([$key1, $key2])->form();

// Validate a form request
PostStoreRequest::factory()->validate()
    ->assertJsonValidationErrors('title');
PostStoreRequest::factory()->validate(['title' => null])
    ->assertJsonMissingValidationErrors('title');

// Get a request instance from factory
$postStoreRequest = PostStoreRequest::factory()->make();
$postStoreRequest = PostStoreRequest::factory()->make(['title' => $title]);

// Set authenticated user for request instance
$postStoreRequest = PostStoreRequest::factory()->asGuest()->make();
$postStoreRequest = PostStoreRequest::factory()->as($user)->make();

// Set method for request
$postStoreRequest = PostStoreRequest::factory()->method($method)->make();

// Set route params
$postStoreRequest = PostStoreRequest::factory()->routeParam($key, $value)->make();

// Set route
$postStoreRequest = PostStoreRequest::factory()->route('welcome')->make();
```
### Resources
Add the `HasResourceFactory` trait to your resource classes:

```php
use Magdonia\LaravelFactories\Concerns\HasResourceFactory;

class YourResource extends JsonResource
{
    use HasResourceFactory;
}
```

Add a factory class for your request in your request factories directory. 

```php
<?php

namespace Tests\RequestFactories;

use Magdonia\LaravelFactories\ResourceFactory;

class AnotherResourceFactory extends ResourceFactory
{
    public function definition(): \Closure
    {
        return function (AssertableJson $json) {
            // Your assertion goes here
        });
    }
}
```

How to use it?
```php
// Get a factory object
$factory = UserResource::factory();

// Set a single model for resource
$factory = UserResource::factory()->model($user);

// Set a collection for resource
$factory = UserResource::factory()->collection($users);

// Set a pagination for resource
$factory = UserResource::factory()->pagination($users);

// Set authenticated user for resource
$factory = UserResource::factory()->user($auth);

// Get a json response from resource
$array = UserResource::factory()->model($user)->json();
$array = UserResource::factory()->collection($users)->json();
$array = UserResource::factory()->pagination($users)->json();

// Get a response object from resource
$testResponse = UserResource::factory()->model($user)->response();
$testResponse = UserResource::factory()->collection($users)->response();
$testResponse = UserResource::factory()->pagination($users)->response();

// Get a resource object from resource
$userResource = UserResource::factory()->model($user)->make();
$userResource = UserResource::factory()->collection($users)->make();
$userResource = UserResource::factory()->pagination($users)->make();

// Assert response
/** @var TestResponse $response */
$response->assertJson(UserResource::factory()->model($user)->create());
$response->assertJson(UserResource::factory()->user($auth)->model($user)->create());
$response->assertJson(UserResource::factory()->collection($users)->create());
$response->assertJson(UserResource::factory()->pagination($users)->create());

// Change wrapper
$response->assertJson(UserResource::factory()->wrapper('anything')->model($user)->create());

// Assertion loaded relations
$response->assertJson(
    PostResource::factory()
    ->model($post->load('creator'))
    ->with('creator', UserResource::class)
    ->create()
);

$response->assertJson(
    UserResource::factory()
    ->model($user->load('posts'))
    ->with('posts', PostCollection::class)
    ->create()
);

// Assertion loaded relations with different relation name
$response->assertJson(
    UserResource::factory()
    ->model($user->load('posts'))
    ->with('latest_posts', PostCollection::class, 'posts')
    ->create()
);
```

> **_NOTE:_** You can modify factory's directory to be anything you want in config.
### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email morteza@protonmail.com instead of using the issue tracker.

## Credits

-   [Morteza Poussaneh](https://github.com/MortezaPoussaneh)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
