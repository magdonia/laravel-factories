<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Requests;

use Magdonia\LaravelFactories\RequestFactory;

/**
 * @extends RequestFactory<SimpleRequestFactory>
 */
class SimpleRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'title' => 'Form title',
        ];
    }

    /**
     * @return $this
     */
    public function withRandom(): self
    {
        $this->set('unique_random', $this->faker->unique()->randomDigit());

        return $this;
    }

    /**
     * @return $this
     */
    public function someState(string $value): self
    {
        $this->set('state_key', $value);

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutTitle(): self
    {
        $this->unset('title');

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function title(string $title): self
    {
        $this->set('title', $title);

        return $this;
    }
}
