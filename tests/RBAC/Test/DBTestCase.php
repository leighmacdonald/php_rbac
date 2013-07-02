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
use RBAC\DataStore\StorageInterface;
use RBAC\Manager\RoleManager;

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

    public function setup_pdo_adapter(StorageInterface $storage_adapter, $init = true) {
        $storage_adapter->getDBConn()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($init) {
            switch(get_class($storage_adapter)) {
                case 'RBAC\DataStore\Adapter\PDOMySQLAdapter':
                    $storage_adapter->getDBConn()->beginTransaction();
                    $queries = [
                        'DROP TABLE IF EXISTS auth_role_permissions',
                        'DROP TABLE IF EXISTS auth_subject_role',
                        'DROP TABLE IF EXISTS auth_role',
                        'DROP TABLE IF EXISTS auth_permission',

                    ];
                    foreach ($queries as $query) {
                        $storage_adapter->getDBConn()->query($query);
                    }
                    $storage_adapter->getDBConn()->commit();
                    $schema_name = "mysql_innodb.sql";
                    break;
                case 'RBAC\DataStore\Adapter\PDOSQLiteAdapter':
                    $schema_name = "sqlite.sql";
                    break;
                default:
                    throw new \Exception("Unsupported testing adapter: " . get_class($storage_adapter));
            }
            $init_status = $storage_adapter->getDBConn()->exec(
                file_get_contents($this->getRootPath() . "/schema/" . $schema_name)
            );
            if ($init_status === null) {
                throw new \PDOException("Failed to setup fixture data");
            }

        }
        $this->conn = $this->createDefaultDBConnection($storage_adapter->getDBConn());
        $this->adapter = $storage_adapter;
        return $this->getConnection();
    }

    final public function getConnection()
    {
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

    protected function getMockManager()
    {
        return new RoleManager($this->getMockDB(), $this->getMockLogger());
    }

    public function getRoleManager()
    {
        return new RoleManager($this->adapter, $this->getMockLogger());
    }
}
