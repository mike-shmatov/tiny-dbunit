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
    private $filePathToTestCase;
    private static $filePathToTestCaseCache;
    
    public function __construct($name = null, array $data = array(), $dataName = '', $child = NULL) {
        parent::__construct($name, $data, $dataName);
        self::$connectorsPool = ConnectionManagement\ConnectorsPoolSingletonDecorator::getInstance();
        self::$sqlRunner = SqlRunners\SqlRunnerSingletonDecorator::getInstance();
        $this->createFilePathToTestCase();
    }
    
    public static function setUpBeforeClass() {
        self::$objId = NULL;
        self::$filePathToTestCaseCache = NULL;
        parent::setUpBeforeClass();
    }
    
    protected static function createTestCaseConnection(){
        self::$testCaseConnectionEnabled = true;
    }
    
    protected static function beforeClassSql(...$sqls){
        self::$beforeClassSql = $sqls;
    }
    
    public function setUp(){
        $this->createTestCaseId();
        if($this->testCaseScopeConnector){
            $this->storeTestCaseScopeConnectorInPool();
        }
        self::$pdoCache = $this->pdo;
        self::$filePathToTestCaseCache = $this->filePathToTestCase;
        parent::setUp();
    }
    
        private function createFilePathToTestCase(){
            $reflection = new \ReflectionClass($this);
            $this->filePathToTestCase = \dirname($reflection->getFileName());
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
                $this->runSql(self::$beforeClassSql);
                self::$beforeClassSql = false;
            }
        }
    
    public static function afterClassSql(...$sqls){
        self::$afterClassSql = $sqls;
    }
    
    public static function tearDownAfterClass() {
        self::$objId = NULL;
        self::$testCaseConnectionEnabled = false;
        if(self::$afterClassSql){
            $sqls = self::normalizeSqls(self::$afterClassSql);
            foreach($sqls as $sql){
                $sql = self::normalizeSql($sql, self::$filePathToTestCaseCache);
                self::$sqlRunner->run(self::$pdoCache, $sql);
            }
            self::$afterClassSql = false;
        }
        self::$filePathToTestCaseCache = NULL; // coverage?
        parent::tearDownAfterClass();
        /* seems to be untestable. have to clear it out in case it was defined 
         * in a test case without tests -- and if so it will persist to next 
         * test because it will not be ever run in useInMemoryConnector()
         * also think it's better to duplicate it in case anybody overriding
         * this method and forgotting to call it as parent::tearDownAfterClass()
         */
        self::$beforeClassSql = ''; // 
    }

    protected function runSql(...$sqls){
        $sqls = self::normalizeSqls($sqls);
        foreach($sqls as $sql){
            $this->runSqlEntry($sql);
        }
    }
    
        private static function normalizeSqls(array $sqls){
            $normalized = [];
            foreach($sqls as $sqlEntry){
                if(is_string($sqlEntry)){
                    $normalized[] = $sqlEntry;
                }
                elseif(is_array($sqlEntry)){
                    $normalized = self::normalizeSqls($sqlEntry);
                }
            }
            return $normalized;
        }
    
        private function runSqlEntry($sql){
            $sql = $this->normalizeSql($sql, $this->filePathToTestCase);
            $this->runSqlWithBatchRunner($sql);
        }
    
        private static function normalizeSql($sql, $relativelyTo){
            if(!file_exists($sql)){
                $sql = self::tryAsRelativePath($sql, $relativelyTo);
            }
            return $sql;
        }
        
            private static function tryAsRelativePath($sql, $relativelyTo){
                $relativeResolved = $relativelyTo.'/'.$sql;
                if(file_exists($relativeResolved)){
                    $sql = \realpath($relativeResolved);
                }
                return $sql;
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