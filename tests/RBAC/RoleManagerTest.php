<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */
namespace Lanified\Test\Auth;

use PDO;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use RBAC\Role\Permission;
use RBAC\Role\Role;
use RBAC\RoleManager;

class RoleManagerTest extends PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $db = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$db == null) {
                $db = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $databases = array_map(
                    function($db) { return $db->Database; },
                    $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_OBJ)
                );
                if (!in_array($GLOBALS['DB_DBNAME'], $databases )) {
                    $db->query("CREATE DATABASE " . $GLOBALS['DB_DBNAME']);
                    $db->query("USE " . $GLOBALS['DB_DBNAME']);
                    $schema_path =  $this->getRootPath() . "/schema/rbac.sql";
                    $schema = file_get_contents($schema_path);
                    $db->query($schema);
                } else {
                    $db->query("USE " . $GLOBALS['DB_DBNAME']);
                }
                self::$db = $db;
            }
            $this->conn = $this->createDefaultDBConnection(self::$db, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }
    protected function getRootPath()
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {

        $fixture_root = $this->getRootPath() . "/tests/fixtures/dataset.xml";
        return $this->createXMLDataSet($fixture_root);
    }

    public function testPermissionCreate()
    {
        $rm = new RoleManager(self::$db);
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm));
        $this->assertTrue($perm->permission_id > 0);
    }

    public function testPermissionDelete()
    {
        $rm = new RoleManager(self::$db);
        $perm = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm));
        $this->assertTrue($rm->permissionDelete($perm));
    }

    public function testRoleCreate()
    {
        $rm = new RoleManager(self::$db);
        $perm1 = Permission::create("test_perm", "description text");
        $perm2 = Permission::create("test_perm1", "description text");
        $this->assertTrue($rm->permissionSave($perm1));
        $this->assertTrue($rm->permissionSave($perm2));
        $role = Role::create("test_role");
        $role->addPermission($perm1);
        $role->addPermission($perm1);
        $role->addPermission($perm2);
        $this->assertEquals(2, sizeof($role->getPermissions()));
        $this->assertTrue($rm->roleSave($role));


    }

    public function testRoleDelete()
    {
        $rm = new RoleManager(self::$db);
        $perm1 = Permission::create("test_perm", "description text");
        $this->assertTrue($rm->permissionSave($perm1));
        $role = Role::create("test_role");
        $role->addPermission($perm1);
        $this->assertTrue($rm->roleSave($role));
        $this->assertTrue($rm->roleDelete($role));
        $this->assertFalse($rm->roleFetchByName("test_role"));
    }
}
