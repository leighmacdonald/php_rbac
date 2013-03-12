<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test;

use RBAC\Exception\ValidationError;
use RBAC\Role\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

class RoleManagerTest extends TestCase
{
    public function setUp()
    {
        $this->getConnection();
        self::$db->query("TRUNCATE auth_role_permissions");
        parent::setUp();
    }

    public function testPermissionFetchById()
    {
        $rm = new RoleManager(self::$db);
        $this->assertEquals(1, $rm->permissionFetchById(1)->permission_id);
    }

    public function testPermissionFetchByInvalidId()
    {
        $rm = new RoleManager(self::$db);
        $this->assertFalse($rm->permissionFetchById(-1));
    }

    public function testPermissionFetch()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $rm = new RoleManager(self::$db);
        $this->assertEquals($count_pre, sizeof($rm->permissionFetch()));
    }

    public function testPermissionSave()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $rm = new RoleManager(self::$db);
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $this->assertTrue($perm->permission_id > 0);
        $perm->name = "test_perm2";
        $this->assertTrue($rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $perm_fetched = $rm->permissionFetchById($perm->permission_id);
        $this->assertEquals($perm_fetched->permission_id, $perm->permission_id);
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

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testPermissionDeleteInvalidId()
    {
        $rm = new RoleManager(self::$db);
        $rm->permissionDelete(new Permission());
    }

    public function testRoleSave()
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
        $role->name = "new_name";
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
