<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const TASK_EDIT = 'task_edit';
    public const TASK_DELETE = 'task_delete';
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::TASK_EDIT, self::TASK_DELETE])
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::TASK_EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return $this->canEdit($task, $user);
            case self::TASK_DELETE:
                // logic to determine if the user can VIEW
                // return true or false
                return $this->canDelete($task, $user);
        }

        return false;
    }

    private function canEdit(Task $task, User $user): bool
    {
        return $user === $task->getUser();
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $user === $task->getUser();
    }
}
