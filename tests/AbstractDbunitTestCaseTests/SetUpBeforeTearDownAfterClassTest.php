<?php
class SetUpBeforeTearDownAfterClassTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        self::createTestCaseConnection();
        self::beforeClassSql('CREATE TABLE CaseTable (txt TEXT);');
        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass() {
        // stubbing so parent does not get called twice
    }


    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function setUp(){
        $this->useInMemoryConnector();
        parent::setUp();
    }
    
    public function testSetUpBefore(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $tables = $results->fetchAll();
        $this->assertCount(1, $tables);
        $this->assertEquals('CaseTable', $tables[0]['name']);
    }
    
    public function testTearDownAfter(){
        self::afterClassSql('DROP TABLE CaseTable;'); // emulate it 
        parent::tearDownAfterClass();
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $tables = $results->fetchAll();
        $this->assertCount(0, $tables);
    }
}
