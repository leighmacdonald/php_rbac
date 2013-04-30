<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test;

use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Test\Mock\MockLogger;
use RBAC\Test\Mock\MockPDO;
use RBAC\Test\Mock\MockPDOStatement;

/**
 *
 */
trait TestTrait
{
    protected $current_perm_num = 10;
    protected $current_role_num = 10;

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

    public function generateRole($name = false, $desc = "blah desc")
    {
        if (!$name) {
            $name = "role_{$this->current_role_num}";
        }
        $role = Role::create($name, $desc);
        $role->role_id = ++$this->current_role_num;
        return $role;
    }

    protected function getMockDB($throw = true)
    {
        $db = new MockPDO($this->getMockStatement($throw), $throw);
        return $db;
    }

    protected function getMockStatement($throw = true)
    {
        $stmt = new MockPDOStatement(false, $throw);
        return $stmt;
    }

    protected function getMockLogger()
    {
        return new MockLogger();
    }
}
