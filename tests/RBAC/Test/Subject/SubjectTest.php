<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test\Subject;

use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Role\RoleSet;
use RBAC\Subject\Subject;
use RBAC\Test\TestCase;

class SubjectTest extends TestCase
{
    public function testConstructor()
    {
        $role_set = new RoleSet([$this->generateRole(), $this->generateRole()]);
        $user_1 = new Subject(1, $role_set);
        $this->assertEquals(1, $user_1->id());
        $this->assertEquals($role_set, $user_1->getRoleSet());

        $user_2 = new Subject(2);
        $this->assertEquals(0, sizeof($user_2->getRoleSet()->getRoles()));
        $this->assertEquals(2, $user_2->id());
    }

    public function testHasPermission()
    {
        $p1 = Permission::create("test_1", "", 1);
        $p2 = Permission::create("test_2", "", 2);
        $p3 = Permission::create("test_4", "", 4);

        $r1 = Role::create("role_1", "", [$p1, $p2]);
        $r2 = Role::create("role_2", "", [$p1, $p2, $p3]);

        $user_1 = new Subject(1, new RoleSet([$r1, $r2]));
        $this->assertTrue($user_1->hasPermission($p1));
        $this->assertTrue($user_1->hasPermission($p2->name));
        $this->assertFalse($user_1->hasPermission("BS_PERM"));
    }

    /**
     * @expectedException \RBAC\Exception\InsufficientPermission
     */
    public function testRequirePermission()
    {
        $p1 = Permission::create("test_1", "", 1);
        $p2 = Permission::create("test_2", "", 2);
        $r1 = Role::create("role_1", "", [$p1, $p2]);
        $user = new Subject(1, new RoleSet([$r1]));
        $user->requirePermission($p1);
        $user->requirePermission($p2->name);
        $user->requirePermission("bs_perm");
    }
}
