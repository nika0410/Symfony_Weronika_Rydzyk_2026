<?php

/**
 * Task fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class TaskFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TaskFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        // 1. Pobieramy wszystkie tagi z bazy (już są załadowane dzięki getDependencies)
        $tags = $this->manager->getRepository(Tag::class)->findAll();

        $this->createMany(100, 'task', function (int $i) use ($tags) {
            $task = new Task();
            $task->setTitle($this->faker->sentence);
            $task->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $task->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $category = $this->getRandomReference('category', Category::class);
            $task->setCategory($category);

            // 2. Logika dodawania losowych tagów
            if (count($tags) > 0) {
                // Wybieramy od 0 do 3 losowych tagów
                $randomTags = $this->faker->randomElements($tags, $this->faker->numberBetween(0, 3));
                foreach ($randomTags as $tag) {
                    $task->addTag($tag);
                }
            }

            return $task;
        });
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class
        ];

    }


}
