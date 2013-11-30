<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

namespace RBAC\DataStore;


use PDO;
use Psr\Log\LoggerAwareInterface;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Subject\SubjectInterface;

/**
 * This interface handles talking to a backend storage server of any kind. Currently its mostly
 * targeted towards RDBMS systems current, however there is no major limitations to adding more
 * backend types such as Document/NoSQL based solutions.
 *
 * The current default supported storage back-ends are making use of foreign key relationships to
 * maintain integrity of the data. Because of this, references to the role in other tables are
 * also deleted/updates. If you are using a DB without foreign key support the changes will
 * need to be accounted for outside of the database backend.
 *
 * Class StorageInterface
 * @package RBAC\DataStore
 */
interface StorageInterface extends LoggerAwareInterface
{
    /**
     * Returns the underlying database connection being used by the interface.
     *
     * @return PDO|mixed
     */
    public function getDBConn();

    /**
     * Save the permission instance  to the data-store. Handles creating new permissions
     * and updating existing permissions.
     *
     * @param Permission $permission Permission to save
     * @return bool
     */
    public function permissionSave(Permission $permission);

    /**
     * Fetch a permission by its unique ID
     *
     * @param int $permission_id
     * @return Permission
     */
    public function permissionFetchById($permission_id);

    /**
     * Fetch all permissions available from the storage backend.
     *
     * @return Permission[]
     */
    public function permissionFetch();

    /**
     * @param Permission $permission
     * @return bool
     */
    public function permissionDelete(Permission $permission);

    /**
     * Commit a role to the backend storage system. Update exiting roles if required, otherwise
     * create a new one.
     *
     * @param Role $role
     * @return bool
     */
    public function roleSave(Role $role);

    /**
     * Save a role to the back-end. If the role does not exist currently, it will be inserted,
     * otherwise changes to existing data will be written.
     *
     * @param Role $role
     * @param Permission $permission
     * @return bool
     */
    public function rolePermissionAdd(Role $role, Permission $permission);

    /**
     * Delete a role from the storage backend.
     *
     * @param Role $role
     * @return bool
     */
    public function roleDelete(Role $role);

    /**
     * Return all the roles from the data-store.
     *
     * @return Role|bool
     */
    public function roleFetch();

    /**
     * Fetch a role by its unique name. return false if no role exists.
     *
     * @param $role_name
     * @return Role|bool
     */
    public function roleFetchByName($role_name);

    /**
     * Fetch and return the role associated with the role ids passed in
     *
     * @param int|int[] $role_ids
     * @return Role|Role[]
     */
    public function roleFetchById($role_ids);

    /**
     * Fetch and return an array of the roles that the subject currently belongs to.
     *
     * @param SubjectInterface $subject
     * @param bool $permissions
     * @return Role[]
     */
    public function roleFetchSubjectRoles(SubjectInterface $subject, $permissions = true);

    /**
     * Associate a subject with the role provided.
     *
     * @param Role $role
     * @param int $subject_id
     * @return bool
     */
    public function roleAddSubjectId(Role $role, $subject_id);

    /**
     * @param Role $role Fetch this roles permissions
     * @return Permission[]
     */
    public function permissionFetchByRole(Role $role);

}
