<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leighm@ppdm.org>
 */
namespace RBAC\Test\DataStore\Adapter;

use MongoClient;
use MongoId;
use RBAC\DataStore\Adapter\MongoAdapter;
use RBAC\Permission;
use RBAC\Test\TestCase;

class MongoAdapterTest extends TestCase
{
    /**
     * @var MongoAdapter
     */
    protected $adapter;

    /**
     * @var \MongoDB
     */
    protected $db;

    /**
     * @var \MongoClient
     */
    protected $client;

    public function setUp()
    {
        $this->client = new MongoClient("mongodb://172.16.1.100:27017");
        $this->adapter = new MongoAdapter($this->client, $this->getMockLogger(), "rbac_test");
        $this->db = $this->client->selectDB('rbac_test');
    }

    public function tearDown()
    {

    }

    public function testPermissionSave()
    {
        $perm_coll = new \MongoCollection($this->db, "auth_permission");
        $p1 = Permission::create("test_1", "desc");
        $this->adapter->permissionSave($p1);
        $this->assertNotEmpty($p1->permission_id);
        $fetched_perm = $perm_coll->findOne(array('_id' => $p1->permission_id));
        $this->assertEquals($fetched_perm['_id']->{'$id'}, $p1->permission_id);
    }
}
