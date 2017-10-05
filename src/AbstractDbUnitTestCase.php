<?php
namespace Tiny\DbUnit;

abstract class AbstractDbUnitTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private static $objId;
    private static $connectorsPool;
    protected $pdo; // can be used in real TestCase, for example as PDO for gateway construction
    private static $sqlRunner;
    protected static $afterClassSql;
    private static $pdoCache;
    private static $testCaseConnectionEnabled = false;
    private $temporaryNewConnector;
    private static $beforeClassSql;
    
    protected static function createTestCaseConnection(){
        self::$testCaseConnectionEnabled = true;
    }
    
    protected static function beforeClassSql($sql){
        self::$beforeClassSql = $sql;
    }


    public function __construct($name = null, array $data = array(), $dataName = '', $child = NULL) {
//        print "hash is ".spl_object_hash($this)."\n";
//        if($child){
//            print "child's ".spl_object_hash($child)."\n";
//        }
        parent::__construct($name, $data, $dataName);
        self::$connectorsPool = ConnectorsPoolSingletonDecorator::getInstance();
        self::$sqlRunner = SqlRunnerSingletonDecorator::getInstance();
    }
    
    public function setUp(){
        $this->createTestCaseId();
        if($this->temporaryNewConnector){
            self::$connectorsPool->store(self::$objId, $this->temporaryNewConnector);
        }
        self::$pdoCache = $this->pdo;
        if(self::$beforeClassSql){
            self::$sqlRunner->run($this->pdo, self::$beforeClassSql);
            self::$beforeClassSql = false;
        }
        parent::setUp();
    }
    
    public static function setUpBeforeClass() {
        self::$objId = NULL;
        parent::setUpBeforeClass();
    }

    protected function setTestCaseScope(){
        $this->createTestCaseId();
    }
    
        private function createTestCaseId(){
            if(is_null(self::$objId)){
                self::$objId = spl_object_hash($this);
            }
//            print "assigned ".self::$objId."\n";
        }

    protected function getConnection() {
        $defaultDbConnection = $this->createDefaultDBConnection($this->pdo);
        return $defaultDbConnection;
    }
    
    protected function useInMemoryConnector(){
        if(self::$testCaseConnectionEnabled){
            $connector = $this->createNewInMemoryConnector();
        }
        else{
            $connector = $this->getConnectionFromPool();
        }
        $pdo = $connector->getPDO();
        $this->fillPdo($pdo);
    }
    
        private function createNewInMemoryConnector(){
            return $this->temporaryNewPdo = self::$connectorsPool->getInMemoryConnector(true);
        }
        
        private function getConnectionFromPool(){
            if(self::$objId && self::$testCaseConnectionEnabled){
                $id = self::$objId;
            }
            else{
                $id = NULL;
            }
            return self::$connectorsPool->getInMemoryConnector($id);
        }
    
    protected function initializeSql($sql){
        static $initialized = false;
        if(!$initialized){
            $this->runSqlWithBatchRunner($sql);
            $initialized = true;
        }
    }
    
    protected function deinitializeSql($sql){
        self::$afterClassSql = $sql;
    }
    
    public static function afterClassSql($sql){
        self::$afterClassSql = $sql;
    }
    
    public static function tearDownAfterClass() {
        self::$objId = NULL;
        self::$testCaseConnectionEnabled = false;
        if(self::$afterClassSql){
            self::$sqlRunner->run(self::$pdoCache, self::$afterClassSql);
            self::$afterClassSql = false;
        }
        parent::tearDownAfterClass();
    }

    protected function runSql($sql){
        $this->runSqlWithBatchRunner($sql);
    }
    
        private function runSqlWithBatchRunner($sql){
            self::$sqlRunner->run($this->pdo, $sql);
            $this->reportOnExceptions();
        }
            private function reportOnExceptions(){
                $collectedExceptions = self::$sqlRunner->getExceptionsCollected();
                if(count($collectedExceptions)){
                    $message = $this->createMessageForCaughtPdoExceptions($collectedExceptions);
                    throw new Exceptions\SqlExecutionException($message);
                }
            }
        
                private function createMessageForCaughtPdoExceptions(array $exs){
                    $desctiption = '';
                    foreach($exs as $pdoException){
                        $desctiption .= $pdoException->getMessage()."\n";
                    }
                $message = "When running provided SQL statments following PDOExceptions were caught:\n\n";
                return $message.$desctiption;
            }

    public function makeInMemoryConnector() {
        $connector = self::$connectorsPool->getInMemoryConnector();
        $this->fillPdo($connector->getPDO());
    }
    
        private function fillPdo($pdo){
            $this->pdo = $pdo;
        }
}