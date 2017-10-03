<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class AbstractDbUnitTestCaseRunningSqlTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    private static $pdoCache;
    
    /**
     * That is how real setUp should look like.
     * Preparing a connection should go first so parent setUp can be run with connection
     * otherwise it will fail.
     * initializeSql() will be run only once
     * runSql() will be run on each setUp()
     */
    public function setUp(){
        $this->useInMemoryConnector();
        $this->initializeSql('CREATE TABLE tbl (id INTEGER PRIMARY KEY AUTOINCREMENT);CREATE TABLE tbl2 (value TEXT);');
        $this->runSql('INSERT INTO tbl2 (value) VALUES ("one");');
        parent::setUp();
        self::$pdoCache = $this->pdo;
    }
    
    public function tearDown(){
        $this->deinitializeSql('DELETE * FROM tbl2;');
    }
    
    /**
     * Stub
     */
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testPdo(){
        $this->assertInstanceOf(\PDO::class, $this->pdo);
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
        $results = $this->pdo->query('SELECT * FROM tbl2;');
        $rows = $results->fetchAll();
        $this->assertArraySubset(['value' => 'one'], $rows[0]);
        $this->assertCount(4, $rows); // note 4 lines since setUpSql was run 4 times including this test
    }
    
//    public static function tearDownAfterClass() {
//        $data = self::$pdoCache->query('SELECT * FROM tbl2;', \PDO::FETCH_ASSOC)->fetchAll();
//        self::assertCount(4, $data);
//        parent::tearDownAfterClass(); // explicit call for deinitialization
//        $data = self::$pdoCache->query('SELECT * FROM tbl2;', \PDO::FETCH_ASSOC)->fetchAll();
//        self::assertCount(0, $data);
//        self::markTestSkipped('todo deinit');
//    }
}