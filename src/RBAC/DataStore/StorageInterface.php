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

interface StorageInterface extends LoggerAwareInterface
{
    /**
     * @return PDO
     */
    public function getDBConn();

    public function permissionSave(Permission $permission);

    public function permissionFetchById($permission_id);

    public function permissionFetch();

    public function permissionDelete(Permission $permission);

    public function roleSave(Role $role);

    public function rolePermissionAdd(Role $role, Permission $permission);

    public function roleDelete(Role $role);

    public function roleFetch();

    public function roleFetchByName($role_name);

    public function roleFetchById($role_ids);

    public function roleFetchSubjectRoles(SubjectInterface $subject);

    public function roleAddSubjectId(Role $role, $subject_id);

    public function permissionFetchByRole(Role $role);

}
