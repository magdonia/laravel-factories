<?php

namespace Magdonia\LaravelFactories\Tests\Factories\Requests;

use Magdonia\LaravelFactories\RequestFactory;

/**
 * @extends RequestFactory<ConfiguredRequestFactory>
 */
class ConfiguredRequestFactory extends RequestFactory
{
    public string $title;

    protected function configure(): void
    {
        $this->title = $this->faker->sentence();
    }

    public function definition(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    /**
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
