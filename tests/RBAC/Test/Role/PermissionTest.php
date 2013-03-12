<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Role;

use RBAC\Role\Permission;
use RBAC\Test\TestCase;

class PermissionTest extends TestCase
{
    public function testToString()
    {
        $this->assertEquals("test_perm", (string) $this->generatePerm("test_perm"));
    }

    public function testCreate()
    {
        $name = "test_name";
        $desc = "test_description";
        $perm = Permission::create($name, $desc);
        $this->assertEquals($name, $perm->name);
        $this->assertEquals($desc, $perm->description);
    }
}
