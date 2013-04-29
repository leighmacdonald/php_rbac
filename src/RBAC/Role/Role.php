<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Role;

use RBAC\Exception\ValidationError;

/**
 * Defines a "Role" that can be assigned to users
 */
class Role
{
    /**
     * @var int|null
     */
    public $role_id;

    /**
     * @var string Unique role name
     */
    public $name;

    /**
     * @var string Role description
     */
    public $description = "";

    public $lft = 0;

    public $rgt = 0;

    /**
     * Array of Permissions belonging to this Role
     *
     * @var Permission[]
     */
    private $permissions = [];

    /**
     * Check if the role allows the permission being requested.
     *
     * @param string $permission Name of Permission being checked
     * @return bool Does this role has the permission
     */
    public function hasPermission($permission)
    {
        if (!$permission) {
            return false;
        }
        foreach ($this->permissions as $perm) {
            if ($perm->name == $permission) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a permission to the roles current permissions if it doesn't already exist
     *
     * @param Permission $permission
     * @return bool
     * @throws \RBAC\Exception\ValidationError Permission has no ID set
     */
    public function addPermission(Permission $permission)
    {
        if (!$permission->permission_id) {
            throw new ValidationError("Permission has invalid state");
        }
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
            return true;
        }
        return false;
    }

    /**
     * Generate a new Permission instance to be saved to the data store.
     *
     * @param string $name Unique name of the role
     * @param string $description Optional description of this role
     * @param Permission[] $permissions Array of initial permissions to add to the role
     * @return Role
     */
    public static function create($name, $description = "", $permissions = [])
    {
        $role = new self();
        $role->name = $name;
        $role->description = $description;
        foreach ($permissions as $permission) {
            $role->addPermission($permission);
        }
        return $role;
    }

    /**
     * Retrieve the permissions currently belonging to this role
     *
     * @return Permission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
