<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Manager;

use PDO;
use Psr\Log\LoggerInterface;
use RBAC\Manager\RoleManager;

class APCRoleManager extends RoleManager
{
    const DEFAULT_TTL = 300;
    const KEY_SEP = "_";

    public function __construct(PDO $db, LoggerInterface $logger = null)
    {
        if (!function_exists('\apc_add')) {
            throw new \BadFunctionCallException("APC not found");
        }
        $this->db = $db;
        $this->log = $logger;
    }

    public function permissionFetchById($permission_id)
    {
        $key = join(self::KEY_SEP, ["perm", $permission_id]);
        $permission = apc_fetch($key);
        if (!$permission) {
            $permission = parent::permissionFetchById($permission_id);
            if ($permission) {
                apc_store("perm_{$permission_id}");
            }
        }
        return $permission;
    }
}
