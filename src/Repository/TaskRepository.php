<?php

/**
 * Task repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TaskRepository.
 *
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    /**
     * Items per page.
     *
     * @var int
     */
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Query all records.
     *
     * @param User|null $author User entity (null returns all tasks)
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(?User $author = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select(
                'partial task.{id, createdAt, updatedAt, title, author}',
                'partial category.{id, title}',
                'partial tags.{id, title}',
                'partial author.{id, email}'
            )
            ->join('task.category', 'category')
            ->leftJoin('task.tags', 'tags')
            ->join('task.author', 'author');

        if ($author instanceof User) {
            $queryBuilder
                ->andWhere('task.author = :author')
                ->setParameter('author', $author);
        }

        return $queryBuilder;
    }

    /**
     * Count tasks by category.
     *
     * @param Category $category Category
     *
     * @return int Number of tasks in category
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('task');

        return $qb->select($qb->expr()->countDistinct('task.id'))
            ->where('task.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
