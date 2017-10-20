<?php
class ConnectorPoolSingletonDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSingleTon(){
        $this->assertSame(\Tiny\DbUnit\ConnectionManagement\ConnectorsPoolSingletonDecorator::getInstance(), \Tiny\DbUnit\ConnectionManagement\ConnectorsPoolSingletonDecorator::getInstance());
    }
}
