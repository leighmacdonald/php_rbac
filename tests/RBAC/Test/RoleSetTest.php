<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test;

use RBAC\Role\RoleSet;
use RBAC\Manager\RoleManager;

class RoleSetTest extends TestCase
{
    public function testHasPermission()
    {
        $rm = new RoleManager(self::$db);
        $role_set = new RoleSet([$rm->roleFetchByName("admin"), $rm->roleFetchByName("guest")]);
        $this->assertTrue($role_set->has_permission("admin_read"));
        $this->assertFalse($role_set->has_permission("bs_perm"));
    }
}
