<?php
/**
 * Category service interface.
 */

namespace App\Service;

use Knp\Component\Pager\Pagination\PaginationInterface;
use App\Entity\Category;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;
    public function delete(Category $category): void;


    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool;


    /**
     * Find by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category entity
     */
    public function findOneById(int $id): ?Category;


}
