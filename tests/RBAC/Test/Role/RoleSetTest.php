<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Role;

use PHPUnit_Framework_TestCase;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Role\RoleSet;
use RBAC\Manager\RoleManager;

class RoleSetTest extends PHPUnit_Framework_TestCase
{
    public function testAddRole()
    {
        $role_a = Role::create("role_a");
        $role_a->role_id = 1;
        $role_b = Role::create("role_b");
        $role_b->role_id = 2;
        $role_set = new RoleSet();
        $this->assertTrue($role_set->addRole($role_a));
        $this->assertFalse($role_set->addRole($role_a));
        $this->assertTrue($role_set->addRole($role_b));
        $roles = $role_set->getRoles();
        $this->assertEquals(2, sizeof($roles));
    }

    public function testHasPermission()
    {
        $admin_read = new Permission();
        $admin_read->permission_id = 1;
        $admin_read->name = "admin_read";
        $role_set = new RoleSet([Role::create("role_a")]);
        $role_b = Role::create("role_b");
        $role_b->addPermission($admin_read);
        $role_set->addRole($role_b);
        $this->assertTrue($role_set->has_permission("admin_read"));
        $this->assertFalse($role_set->has_permission("bs_perm"));
    }

    public function testPermissions()
    {
        $p1 = Permission::create("test_1", "", 1);
        $p2 = Permission::create("test_2", "", 2);
        $p3 = Permission::create("test_3", "", 3);
        $p4 = Permission::create("test_4", "", 4);

        $r1 = Role::create("role_1", "", [$p1, $p2]);
        $r2 = Role::create("role_2", "", [$p1, $p2, $p4]);

        $role_set = new RoleSet([$r1, $r2]);
        $permissions = $role_set->getPermissions();
        $this->assertEquals(3, sizeof($permissions));
    }
}
