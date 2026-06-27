<?php

/**
 * Task service.
 */

namespace App\Service;

use App\Dto\TaskListFiltersDto;
use App\Dto\TaskListInputFiltersDto;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TaskService.
 */
class TaskService implements TaskServiceInterface
{
    /**
     * Items per page.
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param PaginatorInterface       $paginator       Paginator
     * @param TagServiceInterface      $tagService      Tag service
     * @param TaskRepository           $taskRepository  Task repository
     */
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
        private readonly PaginatorInterface $paginator,
        private readonly TagServiceInterface $tagService,
        private readonly TaskRepository $taskRepository
    ) {
    }

    /**
     * Get paginated list.
     *
     * @param int                     $page    Page number
     * @param User|null               $author  Author (null returns all tasks)
     * @param TaskListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?User $author = null, ?TaskListInputFiltersDto $filters = null): PaginationInterface
    {
        $filters = $this->prepareFilters($filters ?? new TaskListInputFiltersDto());

        return $this->paginator->paginate(
            $this->taskRepository->queryAll($author, $filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['task.id', 'task.createdAt', 'task.updatedAt', 'task.title', 'category.title'],
                'defaultSortFieldName' => 'task.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }

    /**
     * Prepare filters for the tasks list.
     *
     * @param TaskListInputFiltersDto $filters Raw filters from request
     *
     * @return TaskListFiltersDto Result filters
     */
    private function prepareFilters(TaskListInputFiltersDto $filters): TaskListFiltersDto
    {
        return new TaskListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );
    }
}
