<?php

namespace App\Traits;

trait HasRoles
{
    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }
} 