<?php
class ConnectorsPoolTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $factoryMock;
    
    public function setUp(){
        $this->factoryMock = $this->createMock(\Tiny\DbUnit\DbConnectorsFactory::class);
        $this->pool = new Tiny\DbUnit\ConnectorsPool($this->factoryMock);
    }
    
    public function testCreating(){
        $this->factoryMock->expects($this->once())
                    ->method('makeInMemoryConnector')
                    ->willReturn('connector');
        $this->assertSame('connector', $this->pool->getInMemoryConnector());
    }
    
    public function testSameInstanceOnNextCall() {
        $connectorStub = new stdClass;
        $this->factoryMock->expects($this->once())
                    ->method('makeInMemoryConnector')
                    ->willReturn($connectorStub);
        $this->assertSame($connectorStub, $this->pool->getInMemoryConnector());
        $this->assertSame($connectorStub, $this->pool->getInMemoryConnector());
    }
}
