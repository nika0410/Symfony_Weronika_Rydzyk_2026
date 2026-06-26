<?php

/**
 * User service.
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
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
     * @param UserRepository              $userRepository User repository
     * @param PaginatorInterface          $paginator      Paginator
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PaginatorInterface $paginator,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['user.id', 'user.email'],
                'defaultSortFieldName' => 'user.id',
                'defaultSortDirection' => 'asc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param User        $user          User entity
     * @param string|null $plainPassword Plain password (optional)
     */
    public function save(User $user, ?string $plainPassword = null): void
    {
        if (null !== $plainPassword && '' !== $plainPassword) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );
        }

        $this->userRepository->save($user);
    }

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Promote user to admin.
     *
     * @param User $user User entity
     */
    public function promoteToAdmin(User $user): void
    {
        $roles = $user->getRoles();
        if (!in_array(UserRole::ROLE_ADMIN->value, $roles, true)) {
            $roles[] = UserRole::ROLE_ADMIN->value;
            $user->setRoles($roles);
            $this->userRepository->save($user);
        }
    }

    /**
     * Demote user from admin.
     *
     * @param User $user User entity
     */
    public function demoteFromAdmin(User $user): void
    {
        $roles = array_filter(
            $user->getRoles(),
            fn (string $role): bool => UserRole::ROLE_ADMIN->value !== $role
        );
        $user->setRoles(array_values($roles));
        $this->userRepository->save($user);
    }


}
