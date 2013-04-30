<?php
/**
 * @package ppdmlib
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test\Mock;

use RBAC\Role\RoleSet;
use RBAC\Subject\SubjectInterface;

class MockUser implements SubjectInterface
{
    private $user_id;

    /**
     * @var RoleSet
     */
    private $roles;

    public function __construct($user_id, RoleSet $roles = null)
    {
        $this->user_id = $user_id;
        $this->roles = ($roles) ? : new RoleSet();
    }

    public function id()
    {
        return $this->user_id;
    }

    public function loadRoleSet(RoleSet $role_set)
    {
        $this->roles = $role_set;
    }

    public function getRoleSet()
    {
        return $this->roles;
    }
}
