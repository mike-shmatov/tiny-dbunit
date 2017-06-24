<?php
class DbConnectorsFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testMakingSqliteInMemoryConnector(){
        $factory = new \Tiny\DbUnit\DbConnectorsFactory();
        $connector = $factory->makeInMemoryConnector();
        $this->assertInstanceOf(\Tiny\DbUnit\SqliteInMemoryDbConnector::class, $connector);
    }
}