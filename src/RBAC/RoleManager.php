<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use RBAC\Exception\ValidationError;
use RBAC\Role\Permission;
use RBAC\Role\Role;
use RBAC\Role\RoleSet;

class RoleManager
{
    const CLASS_ROLE = '\RBAC\Role\Role';

    const CLASS_PERMISSION = '\RBAC\Role\Permission';

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    public function __construct(PDO $db, LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->log = $logger;
    }

    /**
     * @param Permission $permission
     * @return bool
     */
    public function permissionSave(Permission $permission)
    {
        $query = "
            INSERT INTO
                auth_permission (`name`, description, updated_on, added_on)
            VALUES
                (:name, :description, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
              name = :name, description = :description, updated_on = NOW()
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $permission->name, PDO::PARAM_STR, 32);
        $cur->bindParam(":description", $permission->description, PDO::PARAM_STR);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $permission->permission_id = $this->db->lastInsertId();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->log) {
                $this->log->error("Failed to create/update permission", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * Retrieve a Permission instance from the database
     *
     * @param $permission_id
     * @return bool|Permission
     */
    public function permissionFetchById($permission_id)
    {
        $query = "
            SELECT
                permission_id, `name`, description, added_on, updated_on
            FROM
                auth_permission
            WHERE
                permission_id = :permission_id
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":permission_id", $permission_id, PDO::PARAM_INT);
        try {
            $cur->execute();
            return $cur->fetchObject(self::CLASS_PERMISSION);
        } catch (PDOException $db_err) {
            if ($this->log) {
                $this->log->error("Error trying to fetch permission by ID", ['exception' => $db_err]);
            }
            return false;
        }
    }

    /**
     * @internal param $permission_id
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
        return $this->db->query($query)->fetchAll(PDO::FETCH_CLASS, self::CLASS_PERMISSION);
    }

    /**
     * @param Permission $permission
     * @return bool
     */
    public function permissionDelete(Permission $permission)
    {
        $cur = $this->db->prepare("DELETE FROM auth_permission WHERE permission_id = :pid");
        $cur->bindParam(":pid", $permission->permission_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->log) {
                $this->log->error("Failed to delete permission", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function roleSave(Role $role)
    {
        $query = "
            INSERT INTO auth_role
                (name, description, added_on, updated_on) VALUES(:name, :description, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                name = :name, description = :description, updated_on = NOW()
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $role->name, PDO::PARAM_STR, 32);
        $cur->bindParam(":description", $role->description, PDO::PARAM_STR);
        if ($role->role_id) {
            $cur->bindParam(":id", $role->role_id, PDO::PARAM_INT);
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
            if ($this->log) {
                $this->log->error("Failed to save role to DB", ['exception' => $db_err]);
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
     * Add a permission to the provided role.
     *
     * @param Role $role Role to be updated
     * @param Permission $permission Permission to be added
     * @throws Exception\ValidationError
     * @return bool Add success status
     */
    public function rolePermissionAdd(Role $role, Permission $permission)
    {
        if (!$role->role_id or !$permission->permission_id) {
            throw new ValidationError("Invalid Role/Permission state");
        }
        $query = "
            INSERT INTO auth_role_permissions (role_id, permission_id, added_on)
            VALUES (:role_id, :permission_id, NOW())
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
            if ($this->log) {
                $this->log->error("Failed to add permission to role", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * Delete a role from the system permanently. There is no soft delete currently.
     *
     * When deleted successfully the role instance passed in will have its role_id unset
     *
     * @param Role $role Role to be deleted
     * @throws Exception\ValidationError
     * @return bool
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
            if ($this->log) {
                $this->log->error("Failed deleting role", ['exception' => $db_err]);
            }
        }
        return false;
    }

    /**
     * @return Role[]
     */
    public function roleFetch()
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
            if ($this->log) {
                $this->log->error("Failed executing fetch roles", ['exception' => $db_err]);
            }
            return [];
        }
        foreach ($roles as $role) {
            $this->roleLoadPermissions($role);
        }
        return $roles;
    }

    /**
     * @param $role_name
     * @return bool|Role
     */
    public function roleFetchByName($role_name)
    {
        $query = "
            SELECT
                `name`, description, added_on, updated_on
            FROM
                auth_role
            WHERE
                `name` = :name
        ";
        $cur = $this->db->prepare($query);
        $cur->bindParam(":name", $role_name, PDO::PARAM_STR, 32);
        try {
            $cur->execute();
            $role = $cur->fetchObject(self::CLASS_ROLE);
            return ($role) ? $this->roleLoadPermissions($role) : false;
        } catch (PDOException $db_err) {
            if ($this->log) {
                $this->log->error("Failed executing fetch role by name query", ['exception' => $db_err]);
            }
            return false;
        }
    }

    /**
     * If an array of role ids is given, an array of results will be returned.
     *
     * @param int|int[] $role_ids
     * @return bool|Role
     */
    public function roleFetchById($role_ids)
    {
        $multi = is_array($role_ids);
        $role_ids = (array)$role_ids;
        $in_query = join(",", array_fill(0, count($role_ids), "?"));
        $query = "
            SELECT
                role_id, `name`, description, added_on, updated_on
            FROM
                auth_role
            WHERE
                role_id IN('{$in_query}')";

        $cur = $this->db->prepare($query);
        try {
            $cur->execute($role_ids);
            if ($multi) {
                $roles = $cur->fetchAll(PDO::FETCH_CLASS, self::CLASS_ROLE);
                foreach ($roles as $role) {
                    $this->roleLoadPermissions($role);
                }
                return $roles;
            } else {
                $role = $cur->fetchObject(self::CLASS_ROLE);
                $this->roleLoadPermissions($role);
                return $role;
            }
        } catch (PDOException $db_err) {
            if ($this->log) {
                $this->log->error("Failed executing fetch role by id query", ['exception' => $db_err]);
            }
            return false;
        }
    }


    /**
     * Load a user instance with its roleset
     *
     * @param \RBAC\UserInterface $user
     * @return \RBAC\UserInterface
     */
    public function roleLoadUserRoles(UserInterface $user)
    {
        // cache this.
        $roles = $this->roleFetchUserRoles($user);
        $role_set = new RoleSet($roles);
        $user->loadRoleSet($role_set);
        return $user;
    }

    private function roleFetchUserRoles(UserInterface $user)
    {
        $cur = $this->db->prepare("SELECT role_id FROM auth_user_role WHERE user_id = :user_id");
        $cur->bindValue(":user_id", $user->id(), PDO::PARAM_INT);
        try {
            $cur->execute();
            $res = $cur->fetchAll(PDO::FETCH_ASSOC);
            if (!$res) {
                return [];
            }
            return $this->roleFetchById(array_values($res));
        } catch (PDOException $db_err) {
            if ($this->log) {
                $this->log->error("Failed to fetch roles for user", ['exception' => $db_err]);
            }
            return [];
        }
    }

    /**
     * Add a user to an existing role.
     *
     * @param Role $role
     * @param \RBAC\UserInterface $user
     * @return bool
     */
    public function roleAddUser(Role $role, UserInterface $user)
    {
        $query = "INSERT IGNORE INTO auth_user_role (user_id, role_id) VALUES (:user_id, :role_id)";
        $cur = $this->db->prepare($query);
        $cur->bindValue(":user_id", $user->id(), PDO::PARAM_INT);
        $cur->bindParam(":role_id", $role->role_id, PDO::PARAM_INT);
        $this->db->beginTransaction();
        try {
            $cur->execute();
            $this->db->commit();
        } catch (PDOException $db_err) {
            $this->db->rollBack();
            if ($this->log) {
                $this->log->error("Failed to add user to role", ['exception' => $db_err]);
            }
            return false;
        }
        return true;
    }

    /**
     * Fetch all permissions associated with the role provided
     *
     * @param Role $role
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
            if ($this->log) {
                $this->log->error("Error trying to fetch role permissions", ['exception' => $db_err]);
            }
            return [];
        }
        return $permissions;
    }

    /**
     * Load the full permission set into the role instance
     *
     * @param Role $role
     * @return Role
     */
    private function roleLoadPermissions(Role $role)
    {
        foreach ($this->permissionFetchByRole($role) as $permission) {
            $role->addPermission($permission);
        }
        return $role;
    }
}
