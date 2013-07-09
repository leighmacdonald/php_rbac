<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

namespace RBAC\DataStore\Adapter;

/**
 * Provides a storage driver for SQLite based mostly on the MySQL Adapter.
 * Class PDOSQLiteAdapter
 * @package RBAC\DataStore\Adapter
 */
class PDOSQLiteAdapter extends BaseSQLAdapter
{
    protected $sql_time_func = 'datetime(current_timestamp)';
}
