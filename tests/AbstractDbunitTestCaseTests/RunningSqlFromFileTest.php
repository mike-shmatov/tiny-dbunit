<?php
class RunningSqlFromFileTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    public static function setUpBeforeClass() {
        self::configureFilesystemMocking();
        self::beforeClassSql(org\bovigo\vfs\vfsStream::url('root/statements.sql'));
        parent::setUpBeforeClass();
    }
    
    public function setUp(){
        $this->useInMemoryConnector();
        parent::setUp();
    }
    
        private static function configureFilesystemMocking(){
            $rootDir = org\bovigo\vfs\vfsStream::setup('root');
            \org\bovigo\vfs\vfsStream::newFile('statements.sql')
                ->at($rootDir)
                ->setContent('CREATE TABLE sample (field TEXT);');
        }
    
    protected function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testSampleTableInitialized(){
        $results = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='sample';", \PDO::FETCH_ASSOC);
        $this->assertArraySubset(['name' => 'sample'], $results->fetchAll()[0]);
    }
}