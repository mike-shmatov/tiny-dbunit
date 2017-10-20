<?php
class ConnectorsPoolTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $factoryMock;
    
    public function setUp(){
        $this->factoryMock = $this->createMock(\Tiny\DbUnit\ConnectionManagement\DbConnectorsFactory::class);
        $this->pool = new Tiny\DbUnit\ConnectionManagement\ConnectorsPool($this->factoryMock);
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
    
    public function testPlacingConnectorUnderId(){
        $connectorStub = new stdClass();
        $this->factoryMock->expects($this->exactly(2))
                    ->method('makeInMemoryConnector')
                    ->willReturnOnConsecutiveCalls($connectorStub, 'global scope connection');
        $this->assertSame($connectorStub, $this->pool->getInMemoryConnector('id'));
        $this->assertSame($connectorStub, $this->pool->getInMemoryConnector('id'));
        $this->assertSame('global scope connection', $this->pool->getInMemoryConnector());
    }
    
    public function testExplicitStoring(){
        $connectorStub = new stdClass();
        $this->pool->store('id', $connectorStub);
        $this->assertSame($connectorStub, $this->pool->getInMemoryConnector('id'));
        
    }
    
    public function testForceNewConnection(){
        $this->factoryMock->expects($this->exactly(3))
                          ->method('makeInMemoryConnector')
                          ->willReturnOnConsecutiveCalls('connector', 'new connector', 'yet another new');
        $this->assertSame('connector', $this->pool->getInMemoryConnector());
        $this->assertSame('new connector', $this->pool->getInMemoryConnector(true));
        $this->assertSame('yet another new', $this->pool->getInMemoryConnector(true));
    }
}
