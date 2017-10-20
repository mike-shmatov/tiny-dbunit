<?php
class DbConnectorsFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testMakingSqliteInMemoryConnector(){
        $factory = new \Tiny\DbUnit\ConnectionManagement\DbConnectorsFactory();
        $connector = $factory->makeInMemoryConnector();
        $this->assertInstanceOf(\Tiny\DbUnit\ConnectionManagement\SqliteInMemoryDbConnector::class, $connector);
    }
}