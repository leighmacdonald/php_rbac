<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Manager;

use PDO;
use RBAC\DataStore\Adapter\PDOMySQLAdapter;
use RBAC\DataStore\Adapter\PDOSQLiteAdapter;
use RBAC\Manager\RoleManager;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Test\DBTestCase;
use RBAC\Test\RoleManagerTestTrait;


class PDOSQLiteAdapterTest extends DBTestCase
{
    use RoleManagerTestTrait;

    /**
     * @var RoleManager
     */
    protected $rm;

    public function setUp()
    {
        if (extension_loaded('pdo')) {
            if (isset($GLOBALS['DB_DSN_SQLITE'])) {
                $this->setup_pdo_adapter(
                    new PDOSQLiteAdapter(
                        new PDO($GLOBALS['DB_DSN_SQLITE'], null, null, array(PDO::ATTR_PERSISTENT => true)),
                        $this->getMockLogger()
                    )
                );
                parent::setUp();
                $this->rm = $this->getRoleManager();
            } else {
                $this->markTestSkipped("No SQLite global Test DSN set at DB_DSN_SQLITE");
            }
        } else {
            $this->markTestSkipped("PDO module not found");
        }
    }
}
