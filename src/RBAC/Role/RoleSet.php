<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Role;

/**
 * Class RoleSet
 *
 * Collection of roles assigned to a user
 *
 * @package Lanified\Auth
 */
class RoleSet
{
    /**
     * @var Role[]
     */
    private $roles;

    public function __construct(array $roles)
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    public function addRole(Role $role)
    {
        $this->roles[] = $role;
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
