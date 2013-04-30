<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC;

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
     * @var string Unique name of the permission. This is used as its "key"
     */
    public $name;

    /**
     * @var string Description of what the permission provides
     */
    public $description;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @param string $description
     * @param null|int $permission_id
     * @return Permission
     */
    public static function create($name, $description = "", $permission_id = null)
    {
        $perm = new self();
        $perm->name = $name;
        $perm->description = $description;
        $perm->permission_id = $permission_id;
        return $perm;
    }
}
