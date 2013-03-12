<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Role;

use RBAC\Exception\ValidationError;

class Role
{
    public $role_id;
    public $name;
    public $description = "";

    /**
     * @var Permission[]
     */
    private $permissions = [];

    /**
     * @param $permission
     * @internal param $permission_name
     * @return bool
     */
    public function hasPermission($permission)
    {
        foreach ($this->permissions as $perm) {
            if ($perm->name == $permission) {
                return true;
            }
        }
        return false;
    }

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

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function __toString()
    {
        return $this->name;
    }
}
