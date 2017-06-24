<?php
class SqliteInMemoryDbConnectorTest extends PHPUnit_Framework_TestCase
{
    private $connector;
    
    public function setUp(){
        $this->connector = new Tiny\DbUnit\SqliteInMemoryDbConnector();
    }
    
    public function testPDOcreated(){
        $pdo = $this->connector->getPDO();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }
    
    public function testOtherDataIsNull(){
        $this->assertNull($this->connector->getDbHost());
        $this->assertNull($this->connector->getDbName());
        $this->assertNull($this->connector->getDbUser());
        $this->assertNull($this->connector->getDbPassword());
    }
    
    public function testPDOisSame(){
        $this->assertSame($this->connector->getPDO(), $this->connector->getPDO());
    }
}