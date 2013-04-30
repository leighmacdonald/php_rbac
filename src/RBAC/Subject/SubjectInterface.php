<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Subject;

use RBAC\Role\RoleSet;
use RBAC\Permission;

/**
 * To be able to associate your users with roles you must define the following minimal interface
 */
interface SubjectInterface
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

    /**
     * Check if the subject has access to the permission provided.
     *
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermission($permission);
}
