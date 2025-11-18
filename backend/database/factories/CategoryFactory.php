<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'name_ar' => 'فئة ' . $this->faker->word(),
            'slug' => str($name)->slug(),
            'description' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'icon' => 'category-icon-' . $this->faker->numberBetween(1, 20) . '.svg',
            'image_url' => 'https://via.placeholder.com/200',
            'parent_id' => null,
            'order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
