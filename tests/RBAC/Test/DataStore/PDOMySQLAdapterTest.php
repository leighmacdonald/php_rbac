<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace RBAC\Test\Manager;

use PDO;
use RBAC\DataStore\Adapter\PDOMySQLAdapter;
use RBAC\Manager\RoleManager;
use RBAC\Test\DBTestCase;
use RBAC\Test\RoleManagerTestTrait;


class PDOMySQLAdapterTest extends DBTestCase
{
    use RoleManagerTestTrait;

    /**
     * @var RoleManager
     */
    protected $rm;

    public function setUp()
    {
        if (extension_loaded('pdo')) {
            if (isset($GLOBALS['DB_DSN_MYSQL'])) {
                $this->setup_pdo_adapter(
                    new PDOMySQLAdapter(
                        new PDO(
                            $GLOBALS['DB_DSN_MYSQL'],
                            $GLOBALS['DB_DSN_MYSQL_USER'],
                            $GLOBALS['DB_DSN_MYSQL_PASS'],
                            array(PDO::ATTR_PERSISTENT => true)
                        ),
                        $this->getMockLogger()
                    )
                );
                parent::setUp();
                $this->rm = $this->getRoleManager();
            } else {
                $this->markTestSkipped("No MySQL global Test DSN set at DB_DSN_MYSQL");
            }
        } else {
            $this->markTestSkipped("PDO module not found");
        }
    }
}
