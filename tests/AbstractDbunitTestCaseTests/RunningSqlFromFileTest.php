<?php
class RunningSqlFromFileTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        self::beforeClassSql(realpath(__DIR__.'/SqlFiles/SampleTable.sqlite.sql'));
        parent::setUpBeforeClass();
    }
    public function setUp(){
        $this->useInMemoryConnector();
        parent::setUp();
    }
    
    protected function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testSampleTableInitialized(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sample';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'sample'], $results->fetchAll()[0]);
    }
}