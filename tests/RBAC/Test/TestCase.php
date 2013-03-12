<?php
/**
 * @package rules
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test;

use PDO;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Extensions_Database_DataSet_IDataSet;

class TestCase extends PHPUnit_Extensions_Database_TestCase
{

    // only instantiate pdo once for test clean-up/fixture load
    static protected $db = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    protected $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$db == null) {
                $db = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD']);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $databases = array_map(
                    function ($db) {
                        return $db->Database;
                    },
                    $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_OBJ)
                );
                if (!in_array($GLOBALS['DB_DBNAME'], $databases)) {
                    $db->query("CREATE DATABASE " . $GLOBALS['DB_DBNAME']);
                    $db->query("USE " . $GLOBALS['DB_DBNAME']);
                    $schema_path = $this->getRootPath() . "/schema/rbac.sql";
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

    /**
     * Get the root path of the project tree
     *
     * @return string
     */
    protected function getRootPath()
    {
        return dirname(dirname(dirname(dirname(__FILE__))));
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