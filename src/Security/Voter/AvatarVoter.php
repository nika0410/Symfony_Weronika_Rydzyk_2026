<?php

/**
 * Avatar voter.
 */

namespace App\Security\Voter;

use App\Entity\Avatar;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AvatarVoter.
 */
final class AvatarVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @var string
     */
    public const EDIT = 'AVATAR_EDIT';

    /**
     * Delete permission.
     *
     * @var string
     */
    public const DELETE = 'AVATAR_DELETE';

    /**
     * Determines if this voter supports the attribute and subject.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Avatar;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     * @param Vote|null      $vote      Vote object
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Avatar) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit avatar.
     *
     * @param Avatar        $avatar Avatar entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canEdit(Avatar $avatar, UserInterface $user): bool
    {
        return $avatar->getUser() === $user;
    }

    /**
     * Checks if user can delete avatar.
     *
     * @param Avatar        $avatar Avatar entity
     * @param UserInterface $user   User
     *
     * @return bool Result
     */
    private function canDelete(Avatar $avatar, UserInterface $user): bool
    {
        return $avatar->getUser() === $user;
    }
}
