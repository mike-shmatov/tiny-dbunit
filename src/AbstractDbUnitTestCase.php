<?php
namespace Tiny\DbUnit;

abstract class AbstractDbUnitTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private static $connectorsPool;
    protected $pdo; // can be used in real TestCase, for example as PDO for gateway construction
    private $batchRunner;
    private $pdoExceptionsCollector;
    private $parser;
    protected static $deinitializationSql;
    
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        self::$connectorsPool = new ConnectorsPool(new DbConnectorsFactory());
        $this->pdoExceptionsCollector = new \Tiny\DbUnit\PdoExceptionsCollectingHandler();
        $this->batchRunner = new SqlBatchRunner($this->pdo, $this->pdoExceptionsCollector);
        $this->parser = \Tiny\Sql\Parsers\StatementsSplitter::make();
    }
    
    protected function getConnection() {
        $defaultDbConnection = $this->createDefaultDBConnection($this->pdo);
        return $defaultDbConnection;
    }
    
    protected function useInMemoryConnector(){
        $connector = self::$connectorsPool->getInMemoryConnector();
        $pdo = $connector->getPDO();
        $this->fillPdo($pdo);
    }
    
    protected function initializeSql($sql){
        static $initialized = false;
        if(!$initialized){
            if(file_exists($sql)){
                $sql = file_get_contents($sql);
            }
            $this->runSqlWithBatchRunner($sql);
            $initialized = true;
        }
    }
    
    protected function deinitializeSql($sql){
        self::$deinitializationSql = $sql;
    }
    
    public static function tearDownAfterClass() {
        if(self::$deinitializationSql){
            self::$deinitializationSql = NULL;
        }
        parent::tearDownAfterClass();
    }


    protected function runSql($sql){
        $this->runSqlWithBatchRunner($sql);
    }
    
        private function runSqlWithBatchRunner($sql){
            $statements = $this->parser->parse($sql);
            $this->batchRunner->query($statements);
            if($this->pdoExceptionsCollector->hasPdoExceptions()){
                $message = $this->createMessageForCaughtPdoExceptions($this->pdoExceptionsCollector->getCollected(true));
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
            $this->batchRunner->setPdo($this->pdo);
        }
}