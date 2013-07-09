<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

namespace RBAC\DataStore\Adapter;


class PDOMySQLAdapter extends BaseSQLAdapter
{
    protected $sql_time_func = 'NOW()';
}
