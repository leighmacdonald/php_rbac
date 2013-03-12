<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Role;

use PHPUnit_Framework_TestCase;
use RBAC\Role\Permission;
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
}
