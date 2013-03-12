<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Manager;

use RBAC\Exception\ValidationError;
use RBAC\Role\Permission;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;
use RBAC\Test\DBTestCase;

class RoleManagerTest extends DBTestCase
{
    /**
     * @var RoleManager
     */
    protected $rm;

    public function setUp()
    {
        $this->getConnection();
        self::$db->query("TRUNCATE auth_role_permissions");
        parent::setUp();
        $this->rm = $this->getRoleManager();
    }

    protected function getMockManager()
    {
        return new RoleManager($this->getMockDB(), $this->getMockLogger());
    }

    public function getRoleManager()
    {
        return new RoleManager(self::$db, $this->getMockLogger());
    }

    public function testPermissionFetchById()
    {
        $this->assertEquals(1, $this->rm->permissionFetchById(1)->permission_id);
    }


    public function testPermissionFetchByInvalidId()
    {
        $this->assertFalse($this->rm->permissionFetchById(-1));
    }

    public function testPermissionFetchByIdDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionFetchById(1));
    }

    public function testPermissionFetch()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $this->assertEquals($count_pre, sizeof($this->rm->permissionFetch()));
    }

    public function testPermissionFetchDBErr()
    {
        $this->assertEquals([], $this->getMockManager()->permissionFetch());
    }

    public function testPermissionSave()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($this->rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $this->assertTrue($perm->permission_id > 0);
        $perm->name = "test_perm2";
        $this->assertTrue($this->rm->permissionSave($perm));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_permission"));
        $perm_fetched = $this->rm->permissionFetchById($perm->permission_id);
        $this->assertEquals($perm_fetched->permission_id, $perm->permission_id);
    }

    public function testPermissionSaveDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionSave($this->generatePerm()));
    }

    public function testPermissionDelete()
    {
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($this->rm->permissionSave($perm));
        $count_pre = $this->getConnection()->getRowCount("auth_permission");
        $this->assertTrue($this->rm->permissionDelete($perm));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_permission"));
    }

    public function testPermissionDeleteDBErr()
    {
        $this->assertFalse($this->getMockManager()->permissionDelete($this->generatePerm()));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testPermissionDeleteInvalidId()
    {
        $this->rm->permissionDelete(new Permission());
    }

    public function testRoleSave()
    {
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $role = Role::create("test_role");
        $read_perm = $this->rm->permissionFetchById(1);
        $write_perm = $this->rm->permissionFetchById(2);
        $role->addPermission($read_perm);
        $role->addPermission($write_perm);
        $this->assertEquals(2, sizeof($role->getPermissions()));
        $this->assertTrue($this->rm->roleSave($role));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_role"));
        $role->name = "new_name";
        $this->assertTrue($this->rm->roleSave($role));
        $this->assertEquals($count_pre + 1, $this->getConnection()->getRowCount("auth_role"));
    }

    public function testRoleDelete()
    {
        $role = Role::create("test_role");
        $role->addPermission($this->rm->permissionFetchById(1));
        $this->assertTrue($this->rm->roleSave($role));
        $count_pre = $this->getConnection()->getRowCount("auth_role");
        $this->assertTrue($this->rm->roleDelete($role));
        $this->assertEquals($count_pre - 1, $this->getConnection()->getRowCount("auth_role"));
        $this->assertFalse($this->rm->roleFetchByName("admin_read"));
    }

}
