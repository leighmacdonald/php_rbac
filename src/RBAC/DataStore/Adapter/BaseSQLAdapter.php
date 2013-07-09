<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

namespace RBAC\DataStore\Adapter;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use RBAC\DataStore\StorageInterface;
use RBAC\Exception\ValidationError;
use RBAC\Logger;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Subject\SubjectInterface;

abstract class BaseSQLAdapter extends Logger implements StorageInterface
{
    /**
     * Role class  created by
     */
    const CLASS_ROLE = '\RBAC\Role\Role';

    const CLASS_PERMISSION = '\RBAC\Permission';

    protected $sql_time_func = 'NOW()';

    /**
     * @var null|\PDO
     */
    public $db = null;

    /**
     * @param PDO $db
     * @param LoggerInterface $logger
     */
    public function __construct(PDO $db, LoggerInterface $logger = null)
    {
        $this->db = $db;
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @return null|PDO
     */
    public function getDBConn()
    {
        return $this->db;
    }

    /**
     * @param Permission $permission
     *
     * @return bool
     */
    public function permissionSave(Permission $permission)
    {
        if ($permission->permission_id) {
            $query = "
                UPDATE
                    auth_permission
                SET
                    `name` = :name, description = :description, updated_on = " . $this->sql_time_func . "
                WHERE
                    permission_id = :permission_id
            ";
        } else {
            $query = "
                INSERT INTO
                    auth_permission (`name`, description, updated_on, added_on)
                VALUES
                    (:name, :description, " . $this->sql_time_func . ", " . $this->sql_time_func . ")
            ";
        }
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $permission->name, PDO::PARAM_STR, 32);
        $cur->bindParam(":description", $permission->description, PDO::PARAM_STR);
        if ($permission->permission_id) {
            $cur->bindParam(":permission_id", $permission->permission_id, PDO::PARAM_INT);
        }
        $this->db->beginTransaction();
        try {
            $cur->execute();
            if (!$permission->permission_id) {
                $permission->permission_id = $this->db->lastInsertId();
            }
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed to create/update permission", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * Fetch a permission by is ID, returning false if it doesn't exist.
     *
     * @param $permission_id
     *
     * @return bool|Permission|Permission[]
     */
    public function permissionFetchById($permission_id)
    {
        $multi = is_array($permission_id);
        if (!$multi) {
            $permission_id = [$permission_id];
        }
        $in_query = join(',', $permission_id);
        $query = "
            SELECT
                permission_id, `name`, description, added_on, updated_on
            FROM
                auth_permission
            WHERE
                permission_id IN ($in_query)
            ";
        $cur = $this->db->prepare($query);
        try {
            $cur->execute();
            if ($multi) {
                $permissions = $cur->fetchAll(PDO::FETCH_CLASS, self::CLASS_PERMISSION);
            } else {
                $permissions = $cur->fetchObject(self::CLASS_PERMISSION);
            }
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Error trying to fetch permission by ID", ['exception' => $db_err]);
            }
            $permissions = ($multi) ? [] : false;
        }
        return $permissions;
    }

    /**
     * @return Permission[]
     */
    public function permissionFetch()
    {
        $query = "
            SELECT
                permission_id, `name`, description, added_on, updated_on
            FROM
                auth_permission
            ORDER BY
                `name`
        ";
        try {
            return $this->db->query($query)->fetchAll(PDO::FETCH_CLASS, self::CLASS_PERMISSION);
        } catch (PDOException $db_err) {
            $this->logger->error("Database error trying to fetch permissions", ['exception' => $db_err]);
            return [];
        }
    }

    /**
     * @param Permission $permission
     *
     * @return bool
     * @throws \RBAC\Exception\ValidationError
     */
    public function permissionDelete(Permission $permission)
    {
        if (!$permission->permission_id) {
            throw new ValidationError("Permission is in an invalid state");
        }
        $cur = $this->db->prepare("DELETE FROM auth_permission WHERE permission_id = :pid");
        $cur->bindParam(":pid", $permission->permission_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed to delete permission", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * @param Role $role
     *
     * @return bool
     */
    public function roleSave(Role $role)
    {
        if ($role->role_id) {
            $query = "
                UPDATE
                    auth_role
                SET
                    `name` = :name, description = :description, updated_on = " . $this->sql_time_func . "
                WHERE
                    role_id = :role_id
            ";
        } else {
            $query = "
                INSERT INTO auth_role
                    (name, description, added_on, updated_on)
                VALUES
                    (:name, :description, " . $this->sql_time_func . ", " . $this->sql_time_func . ")
            ";
        }
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $role->name, PDO::PARAM_STR, 32);
        $cur->bindParam(":description", $role->description, PDO::PARAM_STR);
        if ($role->role_id) {
            $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        }
        $this->db->beginTransaction();
        try {
            $cur->execute();
            if (!$role->role_id) {
                $role->role_id = (int)$this->db->lastInsertId();
            }
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed to save role to DB", ['exception' => $db_err]);
            }
            return false;
        }
        foreach ($role->getPermissions() as $permission) {
            // TODO: Something on fail
            $this->rolePermissionAdd($role, $permission);
        }

        return true;
    }

    /**
     * @param Role $role
     * @param Permission $permission
     *
     * @return bool
     * @throws \RBAC\Exception\ValidationError
     */
    public function rolePermissionAdd(Role $role, Permission $permission)
    {
        if (!$role->role_id or !$permission->permission_id) {
            throw new ValidationError("Invalid Role/Permission state");
        }
        $query = "
            INSERT INTO
                auth_role_permissions (role_id, permission_id, added_on)
            VALUES
                (:role_id, :permission_id, " . $this->sql_time_func . ")
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        $cur->bindParam(":permission_id", $permission->permission_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed to add permission to role", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * @param Role $role
     *
     * @return bool
     * @throws \RBAC\Exception\ValidationError
     */
    public function roleDelete(Role $role)
    {
        if (!$role->role_id) {
            throw new ValidationError("Invalid role state");
        }
        $query = "
            DELETE FROM
                auth_role
            WHERE
                role_id = :role_id
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
            $role->role_id = null;
            return true;
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed deleting role", ['exception' => $db_err]);
            }
        }
        return false;
    }

    /**
     * @param bool $permissions
     * @return Role[]
     */
    public function roleFetch($permissions = true)
    {
        $query = "
            SELECT
                role_id, `name`, description, added_on, updated_on
            FROM
                auth_role
        ";
        try {
            $roles = $this->db->query($query)->fetchAll(PDO::FETCH_CLASS, self::CLASS_ROLE);
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Failed executing fetch roles", ['exception' => $db_err]);
            }
            $roles = [];
        }

        return $roles;
    }

    /**
     * @param $role_name
     *
     * @return bool|Role
     */
    public function roleFetchByName($role_name)
    {
        $query = "
            SELECT
                r.role_id, r.`name`, r.description, r.added_on, r.updated_on,
                GROUP_CONCAT(p.permission_id) AS _permission_ids
            FROM
                auth_role r
            LEFT OUTER JOIN
                auth_role_permissions p ON p.role_id = r.role_id
            WHERE
                r.`name` = :name
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $role_name, PDO::PARAM_STR, 32);
        try {
            $cur->execute();
            $role = $cur->fetchObject(self::CLASS_ROLE);
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Failed executing fetch role by name query", ['exception' => $db_err]);
            }
            $role = false;
        }
        return ($role->role_id) ? $role : false;
    }

    /**
     * @param $role_ids
     *
     * @return Role|Role[]
     */
    public function roleFetchById($role_ids)
    {
        $multi = is_array($role_ids);
        if (!$role_ids) {
            return [];
        }
        $role_ids = (array)$role_ids;

        $query = "
            SELECT
                r.role_id, r.`name`,r. description, r.added_on, r.updated_on,
                GROUP_CONCAT(p.permission_id) as _permission_ids
            FROM
                auth_role r
            LEFT OUTER JOIN
                auth_role_permissions p ON p.role_id = r.role_id
            WHERE
                r.role_id IN('" . join("','", $role_ids) . "')
            GROUP BY p.role_id
        ";
        try {
            $roles = $this->db->query($query)->fetchAll(PDO::FETCH_CLASS, self::CLASS_ROLE);
            if (!$multi) {
                $roles = (sizeof($roles) > 0) ? $roles[0] : false;
            }
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Failed executing fetch role by id query", ['exception' => $db_err]);
            }
            $roles = [];
        }

        return $roles;
    }

    /**
     * @param SubjectInterface $subject
     *
     * @return Role|\RBAC\Role\Role[]
     */
    public function roleFetchSubjectRoles(SubjectInterface $subject)
    {
        $query = "
            SELECT
                role_id
            FROM
                auth_subject_role
            WHERE
                subject_id = :subject_id
        ";
        $roles = [];
        $cur = $this->db->prepare($query);
        $cur->bindValue(":subject_id", $subject->id(), PDO::PARAM_INT);
        try {
            $cur->execute();
            $res = $cur->fetchAll(PDO::FETCH_OBJ);
            if (!$res) {
                return [];
            } else {
                $role_ids = array_map(
                    function ($row) {
                        return $row->role_id;
                    },
                    $res
                );
                $roles = $this->roleFetchById($role_ids);
            }
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Failed to fetch roles for subject", ['exception' => $db_err]);
            }
        }
        return $roles;
    }

    /**
     * @param Role $role
     * @param      $subject_id
     *
     * @return bool
     * @throws \RBAC\Exception\ValidationError
     */
    public function roleAddSubjectId(Role $role, $subject_id)
    {
        if (!$subject_id) {
            throw new ValidationError("Invalid subject ID");
        }
        $query = "
            INSERT INTO
                auth_subject_role (subject_id, role_id)
            VALUES
                (:subject_id, :role_id)
        ";
        $cur = $this->db->prepare($query);
        $cur->bindValue(":subject_id", $subject_id, PDO::PARAM_INT);
        $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error("Failed to add subject to role", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * @param Role $role
     *
     * @return array
     */
    public function permissionFetchByRole(Role $role)
    {
        $query = "
            SELECT
                p.permission_id, p.name, p.description
            FROM
                auth_permission p
            LEFT JOIN
                auth_role_permissions r ON r.permission_id = p.permission_id
            WHERE r.role_id = :role_id
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        try {
            $cur->execute();
            $permissions = $cur->fetchAll(PDO::FETCH_CLASS, self::CLASS_PERMISSION);
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Error trying to fetch role permissions", ['exception' => $db_err]);
            }
            return [];
        }
        return $permissions;
    }
}
