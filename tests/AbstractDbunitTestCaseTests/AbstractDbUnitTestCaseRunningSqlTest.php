<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class AbstractDbUnitTestCaseRunningSqlTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        self::beforeClassSql('CREATE TABLE tbl (id INTEGER PRIMARY KEY AUTOINCREMENT);CREATE TABLE tbl2 (value TEXT);');
        parent::setUpBeforeClass();
    }
    /**
     * That is how real setUp should look like.
     * Preparing a connection should go first so parent setUp can be run with connection
     * otherwise it will fail.
     * runSql() will be run on each setUp()
     */
    public function setUp(){
        $this->useInMemoryConnector();
        $this->runSql('INSERT INTO tbl2 (value) VALUES ("one");');
        parent::setUp();
    }
    
    /**
     * Stub
     */
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testDbInitializedWithTbl(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tbl';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'tbl'], $results->fetchAll()[0]);
    }
    
    public function testDbInitializedWithTbl2(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tbl2';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'tbl2'], $results->fetchAll()[0]);
    }
    
    public function testSetupOnTbl2() {
        $results = $this->pdo->query('SELECT * FROM tbl2;', \PDO::FETCH_ASSOC);
        $rows = $results->fetchAll();
        $this->assertArraySubset(['value' => 'one'], $rows[0]);
        $this->assertCount(3, $rows); // note 3 rows since setUpSql was run 3 times including this test
    }
}