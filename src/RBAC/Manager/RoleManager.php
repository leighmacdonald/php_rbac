<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Manager;

use PDO;
use PDOException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RBAC\DataStore\StorageInterface;
use RBAC\Exception\ValidationError;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Role\RoleSet;
use RBAC\Subject\SubjectInterface;

class RoleManager implements LoggerAwareInterface
{
    /**
     * Role class  created by
     */
    const CLASS_ROLE = '\RBAC\Role\Role';

    const CLASS_PERMISSION = '\RBAC\Permission';

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
     * @param \PDO $db PDO Database connection
     * @param \RBAC\DataStore\StorageInterface $storage
     * @param \Psr\Log\LoggerInterface $logger optional logger instance
     */
    public function __construct(PDO $db, LoggerInterface $logger = null)
    {
        $this->db = $db;

        if ($logger) {
            $this->setLogger($logger);
        }
    }


    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Save a permission instance to the database.
     *
     * @param Permission $permission
     * @return bool Save execution status
     */
    public function permissionSave(Permission $permission)
    {
        if ($permission->permission_id) {
            $query = "
                UPDATE
                    auth_permission
                SET
                    `name` = :name, description = :description, updated_on = NOW()
                WHERE
                    permission_id = :permission_id
            ";
        } else {
            $query = "
                INSERT INTO
                    auth_permission (`name`, description, updated_on, added_on)
                VALUES
                    (:name, :description, NOW(), NOW())
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
            if ($this->logger) {
                $this->logger->error("Error trying to fetch permission by ID", ['exception' => $db_err]);
            }
            return false;
        }
    }

    /**
     * Fetch an array of all the permissions that exist in the database
     *
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
     * Delete a permission from the database
     *
     * @param Permission $permission
     * @throws \RBAC\Exception\ValidationError
     * @return bool
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
     * Save a role to the database, if the role_id is set, it will be updated
     *
     * @param Role $role
     * @return bool
     */
    public function roleSave(Role $role)
    {
        if ($role->role_id) {
            $query = "
                UPDATE
                    auth_role
                SET
                    `name` = :name, description = :description, updated_on = NOW()
                WHERE
                    role_id = :role_id
            ";
        } else {
            $query = "
                INSERT INTO auth_role
                    (name, description, added_on, updated_on)
                VALUES
                    (:name, :description, NOW(), NOW())
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
     * Add a permission to the provided role.
     *
     * @param Role $role Role to be updated
     * @param Permission $permission Permission to be added
     * @throws \RBAC\Exception\ValidationError
     * @return bool Add success status
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
                (:role_id, :permission_id, NOW())
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
     * Fetch all currently defined roles from the database.
     *
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
            if ($this->logger) {
                $this->logger->error("Failed executing fetch roles", ['exception' => $db_err]);
            }
            return [];
        }
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
        $query = "
            SELECT
                role_id, `name`, description, added_on, updated_on
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
            if ($this->logger) {
                $this->logger->error("Failed executing fetch role by name query", ['exception' => $db_err]);
            }
            return false;
        }
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
        $multi = is_array($role_ids);
        if (!$role_ids) {
            return ($multi) ? [] : false;
        }
        $role_ids = (array)$role_ids;
        if ($multi) {
            $in_query = join(",", array_fill(0, count($role_ids), "?"));
            $query = "
                SELECT
                    role_id, `name`, description, added_on, updated_on
                FROM
                    auth_role
                WHERE
                    role_id IN({$in_query})";
        } else {
            $query = "
                SELECT
                  role_id, `name`, description, added_on, updated_on
                FROM
                    auth_role
                WHERE
                    role_id = :role_id
            ";
        }

        $cur = $this->db->prepare($query);
        try {
            if ($multi) {
                $cur->execute($role_ids);
                $roles = $cur->fetchAll(PDO::FETCH_CLASS, self::CLASS_ROLE);
                foreach ($roles as $role) {
                    $this->roleLoadPermissions($role);
                }
                return $roles;
            } else {
                $cur->bindParam(":role_id", $role_ids[0], PDO::PARAM_INT);
                $cur->execute();
                $role = $cur->fetchObject(self::CLASS_ROLE);
                $this->roleLoadPermissions($role);
                return $role;
            }
        } catch (PDOException $db_err) {
            if ($this->logger) {
                $this->logger->error("Failed executing fetch role by id query", ['exception' => $db_err]);
            }
            return ($multi) ? [] : false;
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
     * Add a user to an existing role.
     *
     * @param Role $role Existing role to be added to
     * @param int $subject_id Initialized user instance
     * @throws \RBAC\Exception\ValidationError
     * @return bool Database execution success status
     */
    public function roleAddSubjectId(Role $role, $subject_id)
    {
        if (!$subject_id) {
            throw new ValidationError("Invalid subject ID");
        }
        $query = "
            INSERT IGNORE INTO
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
     * Fetch all permissions associated with the role provided
     *
     * @param Role $role Initialized role to fetch permissions of
     * @return Permission[] Permissions associated with the role
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

    /**
     * Load the full permission set into the role instance
     *
     * @param Role $role Role to load permissions into
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
