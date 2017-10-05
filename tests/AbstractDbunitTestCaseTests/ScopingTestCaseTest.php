<?php
class ScopingTestCaseTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        self::createTestCaseConnection();
    }
    
    public function setUp() {
        $this->useInMemoryConnector();
        parent::setUp();
    }

    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testNoTablesForConnection(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $this->assertCount(0, $results->fetchAll());
    }
}
