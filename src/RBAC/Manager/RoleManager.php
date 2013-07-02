<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Manager;

use Psr\Log\LoggerInterface;
use RBAC\DataStore\StorageInterface;
use RBAC\Exception\ValidationError;
use RBAC\Logger;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Role\RoleSet;
use RBAC\Subject\SubjectInterface;

class RoleManager extends Logger
{

    /**
     * @var \RBAC\DataStore\StorageInterface
     */
    protected $storage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Setup the required dependencies.
     *
     * The logger can be any PSR-3 compatible logger. Its highly recommended to use one.
     *
     * @param \RBAC\DataStore\StorageInterface $storage
     * @param \Psr\Log\LoggerInterface $logger optional logger instance
     */
    public function __construct(StorageInterface $storage, LoggerInterface $logger = null)
    {
        $this->storage = $storage;

        if ($logger) {
            $this->setLogger($logger);
            $this->storage->setLogger($logger);
        }
    }

    /**
     * Add a user to an existing role. This will insert the added role into the users active RoleSet
     * instance upon successful insertion into the database
     *
     * @param Role $role Existing role to be added to
     * @param \RBAC\Subject\SubjectInterface $subject Initialized subject instance
     * @return bool Database execution success status
     */
    public function roleAddSubject(Role $role, SubjectInterface $subject)
    {
        if ($this->roleAddSubjectId($role, $subject->id())) {
            $users_role_set = $subject->getRoleSet();
            $users_role_set->addRole($role);
            $subject->loadRoleSet($users_role_set);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load a user instance with its corresponding RoleSet
     *
     * @param \RBAC\Subject\SubjectInterface $subject Initialized subject instance
     * @return \RBAC\Subject\SubjectInterface
     */
    public function loadSubjectRoles(SubjectInterface $subject)
    {
        //todo cache this.
        $role_set = new RoleSet($this->roleFetchSubjectRoles($subject));
        $subject->loadRoleSet($role_set);
        return $subject;
    }

    /**
     * Fetch the roles that are associated with the user instance passed in.
     *
     * @todo cache roles
     * @param \RBAC\Subject\SubjectInterface $subject Initialized subject instance
     * @return Role[] Roles the user has assigned
     */
    public function roleFetchSubjectRoles(SubjectInterface $subject)
    {
        return $this->storage->roleFetchSubjectRoles($subject);
    }

    /**
     * Load the full permission set into the role instance
     *
     * @param Role $role Role to load permissions into
     * @return Role
     */
    public function roleLoadPermissions(Role $role)
    {
        foreach ($this->storage->permissionFetchByRole($role) as $permission) {
            $role->addPermission($permission);
        }
        return $role;
    }

    /**
     * Save a permission instance to the database.
     *
     * @param Permission $permission
     * @return bool Save execution status
     */
    public function permissionSave(Permission $permission)
    {
        return $this->storage->permissionSave($permission);
    }

    /**
     * Retrieve a Permission instance from the database
     *
     * @param $permission_id
     * @return bool|Permission
     */
    public function permissionFetchById($permission_id)
    {
        return $this->storage->permissionFetchById($permission_id);
    }

    /**
     * Fetch an array of all the permissions that exist in the database
     *
     * @return Permission[]
     */
    public function permissionFetch()
    {
        return $this->storage->permissionFetch();
    }

    /**
     * Delete a permission from the database
     *
     * @param Permission $permission
     * @throws \RBAC\Exception\ValidationError
     * @return bool
     */
    public function permissionDelete(Permission $permission)
    {
        return $this->storage->permissionDelete($permission);
    }

    /**
     * Save a role to the database, if the role_id is set, it will be updated
     *
     * @param Role $role
     * @return bool
     */
    public function roleSave(Role $role)
    {
        return $this->storage->roleSave($role);
    }

    /**
     * Add a permission to the provided role.
     *
     * @param Role $role Role to be updated
     * @param Permission $permission Permission to be added
     * @throws \RBAC\Exception\ValidationError
     * @return bool Add success status
     */
    public function rolePermissionAdd(Role $role, Permission $permission)
    {
        return $this->storage->rolePermissionAdd($role, $permission);
    }

    /**
     * Delete a role from the system permanently. There is no soft delete currently.
     *
     * When deleted successfully the role instance passed in will have its role_id unset
     *
     * @param Role $role Role to be deleted
     * @throws \RBAC\Exception\ValidationError
     * @return bool
     */
    public function roleDelete(Role $role)
    {
        return $this->storage->roleDelete($role);
    }


    /**
     * Fetch all currently defined roles from the database.
     *
     * @return Role[]
     */
    public function roleFetch()
    {
        $roles = $this->storage->roleFetch();
        foreach ($roles as $role) {
            $this->roleLoadPermissions($role);
        }
        return $roles;
    }

    /**
     * Fetch a role from the database via its unique name.
     *
     * @param string $role_name Role name
     * @return bool|Role
     */
    public function roleFetchByName($role_name)
    {
        $role = $this->storage->roleFetchByName($role_name);
        return ($role) ? $this->roleLoadPermissions($role) : false;
    }

    /**
     *
     * Fetch a role from the database via its role_id attribute.
     *
     * If an array of role ids is given, an array of results will be returned.
     *
     * @param int|int[] $role_ids
     * @return bool|Role
     */
    public function roleFetchById($role_ids)
    {
        $roles = $this->storage->roleFetchById($role_ids);
        if ($roles) {
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    $this->roleLoadPermissions($role);
                }
            } else {
                $this->roleLoadPermissions($roles);
            }
        }
        return $roles;
    }

    /**
     * Add a user to an existing role.
     *
     * @param Role $role Existing role to be added to
     * @param int $subject_id Initialized user instance
     * @throws \RBAC\Exception\ValidationError
     * @return bool Database execution success status
     */
    public function roleAddSubjectId(Role $role, $subject_id)
    {
        return $this->storage->roleAddSubjectId($role, $subject_id);
    }

    /**
     * Fetch all permissions associated with the role provided
     *
     * @param Role $role Initialized role to fetch permissions of
     * @return Permission[] Permissions associated with the role
     */
    public function permissionFetchByRole(Role $role)
    {
        return $this->storage->permissionFetchByRole($role);
    }
}
