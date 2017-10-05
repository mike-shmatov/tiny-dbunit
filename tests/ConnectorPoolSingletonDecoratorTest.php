<?php
class ConnectorPoolSingletonDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSingleTon(){
        $this->assertSame(\Tiny\DbUnit\ConnectorsPoolSingletonDecorator::getInstance(), \Tiny\DbUnit\ConnectorsPoolSingletonDecorator::getInstance());
    }
}
