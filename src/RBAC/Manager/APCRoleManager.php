<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Manager;

use RBAC\Manager\RoleManager;

class APCRoleManager extends RoleManager
{
    const DEFAULT_TTL = 300;
    const KEY_SEP = "_";

    public function permissionFetchById($permission_id)
    {
        $key = join(self::KEY_SEP, ["perm", $permission_id]);
        $permission = apc_fetch($key);
        if (!$permission) {
            $permission = parent::permissionFetchById($permission_id);
            if ($permission) {
                apc_store("perm_{$permission_id}", $permission, self::DEFAULT_TTL);
            }
        }
        return $permission;
    }
}
