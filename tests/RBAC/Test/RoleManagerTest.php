<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test;

use RBAC\Role\Permission;
use RBAC\Role\Role;
use RBAC\RoleManager;

class RoleManagerTest extends TestCase
{
    public function testPermissionCreate()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $rm = new RoleManager(self::$db);
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $this->assertTrue($perm->permission_id > 0);
    }

    public function testPermissionDelete()
    {
        $rm = new RoleManager(self::$db);
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm));
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $this->assertTrue($rm->permissionDelete($perm));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_permission"));
    }

    public function testRoleCreate()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $rm = new RoleManager(self::$db);
        $role = Role::create("test_role");
        $read_perm = $rm->permissionFetchById(1);
        $write_perm = $rm->permissionFetchById(2);
        $role->addPermission($read_perm);
        $role->addPermission($write_perm);
        $this->assertEquals(2, sizeof($role->getPermissions()));
        $this->assertTrue($rm->roleSave($role));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_role"));
    }

    public function testRoleDelete()
    {
        $rm = new RoleManager(self::$db);
        $role = Role::create("test_role");
        $role->addPermission($rm->permissionFetchById(1));
        $this->assertTrue($rm->roleSave($role));
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $this->assertTrue($rm->roleDelete($role));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_role"));
        $this->assertFalse($rm->roleFetchByName("admin_read"));
    }
}
