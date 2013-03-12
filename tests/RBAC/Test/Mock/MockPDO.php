<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Mock;

use PDO;
use PDOException;

/**
 * You cannot Mock built in classes which require constructor args easily. This removes this
 * limitation.
 *
 * $pdoMock = $this->getMock('MockPDO', array('prepare'));
 *
 */
class MockPDO extends PDO
{
    public function __construct($mock_stmt, $throw = true, $throw_msg = "Test Exception Thrown")
    {
        $this->mock_stmt = $mock_stmt;
        $this->throw = $throw;
        $this->throw_msg = $throw_msg;
    }

    public function prepare($statement, $options = null)
    {
        return $this->mock_stmt;
    }

    public function beginTransaction()
    {
        return true;
    }

    public function query($statement)
    {
        return $this->mock_stmt;
    }

    public function rollBack()
    {
        return true;
    }

    public function commit()
    {
        if ($this->throw) {
            throw new PDOException($this->throw_msg);
        }
        return true;
    }

    public function lastInsertId($name = null)
    {
        return 1;
    }
}
