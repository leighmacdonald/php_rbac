<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Manager;

use PDO;
use RBAC\DataStore\Adapter\PDOPgAdapter;
use RBAC\Manager\RoleManager;
use RBAC\Permission;
use RBAC\Role\Role;
use RBAC\Test\DBTestCase;
use RBAC\Test\RoleManagerTestTrait;


class PDOPGAdapterTest extends DBTestCase
{
    use RoleManagerTestTrait;

    /**
     * @var RoleManager
     */
    protected $rm;

    public function setUp()
    {
        if (extension_loaded('pdo_pgsql') and extension_loaded('pgsql')) {
            if (isset($GLOBALS['DB_DSN_PG'])) {
                $this->setup_pdo_adapter(
                    new PDOPgAdapter(
                        new PDO(
                            $GLOBALS['DB_DSN_PG'],
                            $GLOBALS['DB_DSN_PG_USER'],
                            $GLOBALS['DB_DSN_PG_PASS'],
                            array(PDO::ATTR_PERSISTENT => true)),
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
