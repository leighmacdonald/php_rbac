<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC;

use RBAC\Role\RoleSet;

/**
 * To be able to associate your users with roles you must define the following minimal interface
 */
interface UserInterface
{
    /**
     * This should return the unsigned unique ID of the user instance.
     *
     * @return int user_id of the user
     */
    public function id();

    /**
     * Load the users RoleSet defining what the user has access to
     *
     * @param \RBAC\Role\RoleSet $role_set
     */
    public function loadRoleSet(RoleSet $role_set);

    /**
     * @return \RBAC\Role\RoleSet
     */
    public function getRoleSet();
}
