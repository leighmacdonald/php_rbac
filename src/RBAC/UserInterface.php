<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC;

use RBAC\Role\RoleSet;

interface UserInterface
{
    /**
     * @return int
     */
    public function id();

    public function loadRoleSet(RoleSet $role_set);
}
