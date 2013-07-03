<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Mock;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use RBAC\DataStore\StorageInterface;
use RBAC\Exception\StorageError;
use RBAC\Exception\ValidationError;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Subject\SubjectInterface;

/**
 * You cannot Mock built in classes which require constructor args easily. This removes this
 * limitation.
 *
 * $pdoMock = $this->getMock('MockStorage', array('prepare'));
 *
 */
class MockStorage extends PDO implements StorageInterface
{
    public function __construct($mock_stmt, $throw = true, $throw_msg = "Test Exception Thrown")
    {
        $this->mock_stmt = $mock_stmt;
        $this->throw = $throw;
        $this->throw_msg = $throw_msg;
    }

    /**
     * @return null|PDO
     */
    public function getDBConn()
    {
        return $this;
    }

    public function prepare($statement, $options = null)
    {
        return $this->mock_stmt;
    }

    public function beginTransaction()
    {
        return true;
    }

    public function query($statement)
    {
        return $this->mock_stmt;
    }

    public function rollBack()
    {
        return true;
    }

    public function commit()
    {
        if ($this->throw) {
            throw new StorageError($this->throw_msg);
        }
        return true;
    }

    public function lastInsertId($name = null)
    {
        return 1;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        // TODO: Implement setLogger() method.
    }

    public function permissionSave(Permission $permission)
    {
        return false;
    }

    public function permissionFetchById($permission_id)
    {
        return false;
    }

    public function permissionFetch()
    {
        return [];
    }

    public function permissionDelete(Permission $permission)
    {
        return false;
    }

    public function roleSave(Role $role)
    {
        return false;
    }

    public function rolePermissionAdd(Role $role, Permission $permission)
    {
        return false;
    }

    public function roleDelete(Role $role)
    {
        if (!$role->role_id) {
            throw new ValidationError();
        }
        return false;
    }

    public function roleFetch()
    {
        return [];
    }

    public function roleFetchByName($role_name)
    {
        return false;
    }

    public function roleFetchById($role_ids)
    {
        return is_array($role_ids) ? [] : false;
    }

    public function roleFetchSubjectRoles(SubjectInterface $subject)
    {
        return [];
    }

    public function roleAddSubjectId(Role $role, $subject_id)
    {
        return false;
    }

    public function permissionFetchByRole(Role $role)
    {
        return [];
    }
}
