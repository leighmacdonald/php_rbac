<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Mock;

class MockPDOStatement extends \PDOStatement
{
    public function __construct($mock_stmt = false, $throw = true, $throw_msg = "Test Exception Thrown")
    {
        $this->mock_stmt = $mock_stmt;
        $this->throw = $throw;
        $this->throw_msg = $throw_msg;
    }

    public function execute($bound_input_params = null)
    {
        if ($this->throw) {
            throw new \PDOException($this->throw_msg);
        }
        return true;
    }

    public function fetchAll($how = null, $class_name = null, $ctor_args = null)
    {
        if ($this->throw) {
            throw new \PDOException($this->throw_msg);
        }
        return [];
    }
}
