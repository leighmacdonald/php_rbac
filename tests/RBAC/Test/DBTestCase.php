<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test;

use PDO;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use RBAC\DataStore\Adapter\PDOMySQLAdapter;
use RBAC\DataStore\Adapter\PDOSQLiteAdapter;
use RBAC\DataStore\StorageInterface;

/**
 * SPEED TIP:
 * http://dev.mysql.com/doc/refman/5.5/en/innodb-parameters.html#sysvar_innodb_flush_log_at_trx_commit
 * Set this to 2
 */
class DBTestCase extends PHPUnit_Extensions_Database_TestCase
{
    use TestTrait;

    /**
     * @var StorageInterface
     */
    public $adapter = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    protected $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if ($this->adapter == null) {
                $db = new PDO($GLOBALS['DB_DSN'], null, null, array(PDO::ATTR_PERSISTENT => true));
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $schema_path = $this->getRootPath() . "/schema/sqlite.sql";
                $schema = file_get_contents($schema_path);
                $db->exec($schema);

                $this->adapter = new PDOSQLiteAdapter($db, $this->getMockLogger());
            }
            $this->conn = $this->createDefaultDBConnection($db);
        }
        return $this->conn;
    }


    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $fixture_root = $this->getRootPath() . "/tests/fixtures/dataset.xml";
        return $this->createXMLDataSet($fixture_root);
    }
}
