<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test;

use RBAC\Role\Permission;

/**
 *
 */
trait TestTrait
{
    protected $current_perm_num = 0;

    /**
     * Get the root path of the project tree
     *
     * @return string
     */
    protected function getRootPath()
    {
        return dirname(dirname(dirname(dirname(__FILE__))));
    }

    protected function generatePerm($name = false)
    {
        if (!$name) {
            $name = "perm_{$this->current_perm_num}";
        }
        $perm = Permission::create($name);
        $perm->permission_id = ++$this->current_perm_num;
        return $perm;
    }
}
