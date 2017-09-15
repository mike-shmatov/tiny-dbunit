<?php
namespace Tiny\DbUnit;

abstract class AbstractDbUnitTestCase extends \PHPUnit_Extensions_Database_TestCase implements Interfaces\ConnectorsFactory
{
    private static $connectorsFactory;
    private static $connector;
    protected $pdo; // can be used in real TestCase, for example as PDO for gateway construction
    protected static $defaultDbConnection;
    
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        self::$connectorsFactory = new DbConnectorsFactory();
    }
    
    public function setUp(){
        $this->fillPdo();
        parent::setUp();
    }
    
    protected function getConnection() {
        if(is_null(self::$defaultDbConnection)){
            self::$defaultDbConnection = $this->createDefaultDBConnection(self::$connector->getPDO());
        }
        return self::$defaultDbConnection;
    }


    public function makeInMemoryConnector() {
        if(is_null(self::$connector)){
            self::$connector = self::$connectorsFactory->makeInMemoryConnector();
        }
        $this->fillPdo();
    }
    
        private function fillPdo(){
            $this->pdo = self::$connector->getPDO();
        }
}