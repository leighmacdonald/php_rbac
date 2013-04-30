<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Role;

use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Test\TestCase;

class RoleTest extends TestCase
{
    public function testCreate()
    {
        $role = Role::create("role_a", "", [$this->generatePerm(), $this->generatePerm()]);
        $this->assertEquals(2, sizeof($role->getPermissions()));
    }

    public function testHasPermission()
    {
        $role = Role::create("role_a", "", [$this->generatePerm("perm_a")]);
        $this->assertTrue($role->hasPermission("perm_a"));
        $this->assertFalse($role->hasPermission(""));
        $this->assertFalse($role->hasPermission("bs_perm"));
    }

    /**
     * @expectedException \RBAC\Exception\ValidationError
     */
    public function testAddInvalidPermission()
    {
        $role = Role::create("role_a");
        $role->addPermission(Permission::create("invalid"));
    }

    public function testAddDuplicatePermission()
    {
        $perm = $this->generatePerm();
        $role = Role::create("role_a");
        $this->assertTrue($role->addPermission($perm));
        $this->assertFalse($role->addPermission($perm));
    }

    public function testToString()
    {
        $this->assertEquals("test_role", (string)Role::create("test_role"));
    }
}
