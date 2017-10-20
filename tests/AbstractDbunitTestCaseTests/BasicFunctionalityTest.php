<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class BasicFunctionalityTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    
    public static function setUpBeforeClass() {
        self::beforeClassSql('CREATE TABLE tbl (id INTEGER PRIMARY KEY AUTOINCREMENT, value TEXT);CREATE TABLE tbl2 (value TEXT);');
        parent::setUpBeforeClass();
    }
    
    /**
     * That is how real setUp should look like.
     * Preparing a connection should go first so parent setUp can be run with connection
     * otherwise it will fail.
     */
    public function setUp(){
        $this->useInMemoryConnector();
        $this->runSql('INSERT INTO tbl2 (value) VALUES ("one");');
        parent::setUp();
    }
    
    public function tearDown(){
        $this->runSql('DELETE FROM tbl2;');
    }
    
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'tbl' =>
            [
                ['value' => 42]
            ]
        ]);
    }
    
    public function testDbInitializedWithTbl(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tbl';", \PDO::FETCH_ASSOC);
        $tables = array_column($results->fetchAll(), 'name');
        $this->assertEquals('tbl', $tables[0]);
    }
    
    public function testPHPDbunitAccessesTbl(){
        $results = $this->pdo->query('SELECT * FROM tbl;', \PDO::FETCH_ASSOC);
        $rows = $results->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertSame('42', $rows[0]['value']);
    }
    
    public function testDbInitializedWithTbl2(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tbl2';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'tbl2'], $results->fetchAll()[0]);
    }
    
    public function testSetUpAndTearDownForTbl2() {
        $results = $this->pdo->query('SELECT * FROM tbl2;', \PDO::FETCH_ASSOC);
        $rows = $results->fetchAll();
        $this->assertArraySubset(['value' => 'one'], $rows[0]);
        $this->assertCount(1, $rows); // covers both setUp and tearDown
    }
}