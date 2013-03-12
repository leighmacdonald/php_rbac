<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Role;

/**
 * Class RoleSet
 *
 * Provides a wrapper around a collection of Roles
 *
 * @package Lanified\Auth
 */
class RoleSet
{
    /**
     * @var Role[]
     */
    private $roles = [];

    /**
     * Setup the default data set
     *
     * @param Role[] $roles Initial role data set to populate the class with
     */
    public function __construct(array $roles = [])
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    /**
     * Add a new role to the role set
     *
     * @param Role $role
     * @return bool
     */
    public function addRole(Role $role)
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
            return true;
        }
        return false;
    }

    /**
     * @todo accept Permission class or permission_id as well for identifiers?
     * @param $permission_name
     * @return bool
     */
    public function has_permission($permission_name)
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission_name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
