<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Role;

/**
 * Class Permission
 *
 * Defines a permission key that can then be assigned to a role
 *
 */
class Permission
{
    /**
     * @var int
     */
    public $permission_id;

    /**
     * @var string Name of the permission key
     */
    public $name;

    /**
     * @var string Description of what the permission provides
     */
    public $description;

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @param string $description
     * @return Permission
     */
    public static function create($name, $description = "")
    {
        $perm = new self();
        $perm->name = $name;
        $perm->description = $description;
        return $perm;
    }
}
