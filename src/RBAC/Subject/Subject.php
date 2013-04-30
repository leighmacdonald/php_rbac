<?php
namespace RBAC\Subject;

use RBAC\Exception\InsufficientPermission;
use RBAC\Permission;
use RBAC\Role\RoleSet;


/**
 * Example of an implementation of the SubjectInterface to demonstrate using a custom user class.
 *
 *
 * Class Subject
 * @package RBAC\Subject
 */
class Subject implements SubjectInterface
{
    /**
     * ID of the user. This should be a unique integer supplied yourself from whatever
     * user management system you are using.
     *
     * @var int
     */
    protected $subject_id;

    /**
     * A class holding a current set of the users roles and permissions they belong to.
     *
     * @var RoleSet
     */
    private $roles;

    /**
     * @param int $user_id A user id to tied into your own user management system
     * @param RoleSet $role_set (optional) A RoleSet supplied in the constructor
     */
    public function __construct($subject_id, RoleSet $role_set = null)
    {
        $this->subject_id = $subject_id;
        if (!$role_set) {
            $role_set = new RoleSet();
        }
        $this->loadRoleSet($role_set);
    }

    /**
     * This should return the unsigned unique ID of the user instance.
     *
     * @return int user_id of the user
     */
    public function id()
    {
        return $this->subject_id;
    }

    /**
     * Load the users RoleSet defining what the user has access to
     *
     * @param \RBAC\Role\RoleSet $role_set
     */
    public function loadRoleSet(RoleSet $role_set)
    {
        $this->roles = $role_set;
    }

    /**
     * Return the currently loaded RoleSet
     *
     * @return \RBAC\Role\RoleSet
     */
    public function getRoleSet()
    {
        return $this->roles;
    }

    /**
     * Check if a user has access to the permission requested
     *
     * @param string|Permission $permission name of the permission or Permission instance
     * @return bool
     */
    public function hasPermission($permission)
    {
        if ($permission instanceof Permission) {
            $permission = $permission->name;
        }
        return $this->getRoleSet()->has_permission($permission);
    }

    /**
     * Check for the permissions existence for the subject, throwing an exception if its
     * not found.
     *
     * @param string|Permission $permission
     * @throws \RBAC\Exception\InsufficientPermission
     */
    public function requirePermission($permission)
    {
        if (!$this->hasPermission($permission)) {
            throw new InsufficientPermission(
                "Insufficient permission to complete your request: {$permission}]"
            );
        }
    }
}
