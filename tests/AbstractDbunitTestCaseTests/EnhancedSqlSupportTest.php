<?php
class EnhancedSqlSupportTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        static::createTestCaseConnection();
        static::beforeClassSql('../FileFixtures/sample-table-schema.sql', 'CREATE TABLE sample2 (id INTEGER);');
        parent::setUpBeforeClass();
    }
    
    public static function tearDownAfterClass() {
        self::afterClassSql([
            'DROP TABLE sample2;',
            'files' => [
                '../FileFixtures/drop-sample-table.sql'
            ] // nested array works, too
        ]);
        parent::tearDownAfterClass();
    }
    
    public function setUp(){
        $this->useInMemoryConnector();
        $this->runSql('CREATE TABLE temp1 (id INTEGER);', 'CREATE TABLE temp2 (id INTEGER);');
        parent::setUp();
    }
    
    public function tearDown(){
        $this->runSql([
            'DROP TABLE temp1;', 
            'DROP TABLE temp2;'
        ]);
    }
    
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'sample' => [
                ['id' => 1, 'field' => 'text'],
                ['id' => 2, 'field' => 'string']
            ]
        ]);
    }

    public function testRowsCount(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('sample'));
    }
    
    public function testTempTablesExist(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $tables = array_column($results->fetchAll(), 'name');
        $this->assertEquals(4, count($tables));
    }
}
