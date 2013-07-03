<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC;

/**
 * Class Permission
 * Defines a permission key that can then be assigned to a role.

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
     * Configure and return a new Permission instance ready to be saved to the backend data store
     *
     * @param string $name          Name if the permission, this is treated as a key and must be unique
     * @param string $description   Optional description.
     * @param null|int $permission_id Optional permission ID
     *
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
