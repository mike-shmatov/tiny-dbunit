<?php
class TestCaseRunningSqlFromFileTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public function setUp(){
        $this->useInMemoryConnector();
        $this->initializeSql(realpath(__DIR__.'/SqlFiles/SampleTable.sqlite.sql'));
    }
    
    protected function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testSampleTableInitialized(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sample';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'sample'], $results->fetchAll()[0]);
    }
}
