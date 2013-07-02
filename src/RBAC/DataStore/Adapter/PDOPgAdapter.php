<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

namespace RBAC\DataStore\Adapter;


use RBAC\DataStore\StorageInterface;
use RBAC\Permission;
use RBAC\Role\Role;

/**
 * Provides a storage driver for SQLite based mostly on the MySQL Adapter.
 * Class PDOSQLiteAdapter
 * @package RBAC\DataStore\Adapter
 */
class PDOPgAdapter extends PDOMySQLAdapter implements StorageInterface
{
    protected $sql_time_func = 'datetime(current_timestamp)';
}
