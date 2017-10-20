<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class ConnectionAvailabilityTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    /**
     * That is how real setUp should look like.
     * Preparing a connection should go first so parent setUp can be run with connection
     * otherwise it will fail.
     */
    public function setUp(){
        $this->useInMemoryConnector();
        parent::setUp();
    }
    
    /**
     * Stub
     */
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testConnectionIsGlobal(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $tables = array_column($results->fetchAll(), 'name');
        $this->assertContains('global', $tables);
    }
    
    public function testHavingPdo(){
        $this->assertInstanceOf(\PDO::class, $this->pdo);
    }
    
    public function testGetPHPUnitConnection(){
        $this->assertInstanceOf(\PHPUnit_Extensions_Database_DB_IDatabaseConnection::class, $this->getConnection());
    }
    
    public function testConnectionIsNotCached(){
        $this->assertNotSame($this->getConnection(), $this->getConnection());
    }
}