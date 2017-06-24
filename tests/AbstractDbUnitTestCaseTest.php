<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class AbstractDbUnitTestCaseTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    /**
     * That is how real setUp should look like.
     */
    public function setUp(){
        $this->makeInMemoryConnector();
        parent::setUp();
    }
    
    /**
     * Stub
     */
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testHavingPdo(){
        $this->assertInstanceOf(\PDO::class, $this->pdo);
    }
    
    public function testGetConnection(){
        $this->assertInstanceOf(\PHPUnit_Extensions_Database_DB_IDatabaseConnection::class, $this->getConnection());
    }
    
    public function testConnectionIsCached(){
        $this->assertSame($this->getConnection(), $this->getConnection());
    }
}