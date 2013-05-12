<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */

namespace RBAC\DataStore\Adapter;


use RBAC\DataStore\StorageInterface;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Subject\SubjectInterface;

class PDOSQLiteAdapter extends PDOMySQLAdapter implements StorageInterface
{
    protected $sql_time_func = 'datetime(current_timestamp)';
}
