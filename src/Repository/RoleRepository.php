<?php
namespace App\Repository;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class RoleRepository
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function getAllRoles(): array
    {
        return $this->roleHierarchy->getReachableRoleNames(['ROLE_ADMIN']);
    }

    public function getRolesBelow(string $role): array
    {
        return $this->roleHierarchy->getReachableRoleNames([$role]);
    }
}