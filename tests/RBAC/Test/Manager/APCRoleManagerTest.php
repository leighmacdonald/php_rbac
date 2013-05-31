<?php
/**
 * @package rules
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Manager;

use RBAC\Manager\APCRoleManager;

class APCRoleManagerTest extends RoleManagerTest
{
    public function getRoleManager()
    {
        return new APCRoleManager($this->adapter, $this->getMockLogger());
    }
}
