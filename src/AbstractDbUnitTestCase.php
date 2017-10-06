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
    private $testCaseScopeConnector;
    private static $beforeClassSql;
    
    public function __construct($name = null, array $data = array(), $dataName = '', $child = NULL) {
        parent::__construct($name, $data, $dataName);
        self::$connectorsPool = ConnectorsPoolSingletonDecorator::getInstance();
        self::$sqlRunner = SqlRunnerSingletonDecorator::getInstance();
    }
    
    public static function setUpBeforeClass() {
        self::$objId = NULL;
        parent::setUpBeforeClass();
    }
    
    protected static function createTestCaseConnection(){
        self::$testCaseConnectionEnabled = true;
    }
    
    protected static function beforeClassSql($sql){
        self::$beforeClassSql = $sql;
    }
    
    public function setUp(){
        $this->createTestCaseId();
        if($this->testCaseScopeConnector){
            $this->storeTestCaseScopeConnectorInPool();
        }
        self::$pdoCache = $this->pdo;
        parent::setUp();
    }
    
        private function createTestCaseId(){
            if(is_null(self::$objId)){
                self::$objId = spl_object_hash($this);
            }
        }
    
        private function storeTestCaseScopeConnectorInPool(){
            self::$connectorsPool->store(self::$objId, $this->testCaseScopeConnector);
            $this->testCaseScopeConnector = NULL;
        }

    protected function getConnection() {
        $defaultDbConnection = $this->createDefaultDBConnection($this->pdo);
        return $defaultDbConnection;
    }
    
    protected function useInMemoryConnector(){
        $connector = $this->resolveInMemoryConnector();
        $this->pdo = $connector->getPDO();
        $this->runBeforeClassSql();
    }
    
        private function resolveInMemoryConnector(){
            if(self::$testCaseConnectionEnabled && !self::$objId){
                $connector = $this->createNewInMemoryConnectorForTestCaseScope();
            }
            else{
                $connector = $this->getConnectionFromPool();
            }
            return $connector;
        }
    
            private function createNewInMemoryConnectorForTestCaseScope(){
                return $this->testCaseScopeConnector = self::$connectorsPool->getInMemoryConnector(true);
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
        
        private function runBeforeClassSql(){
            if(self::$beforeClassSql){
                self::$sqlRunner->run($this->pdo, self::$beforeClassSql);
                self::$beforeClassSql = false;
            }
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
}