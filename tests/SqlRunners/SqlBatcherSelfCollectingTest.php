<?php
class SqlBatcherSelfCollectingTest extends PHPUnit_Framework_TestCase
{
    private $batchRunner;
    private $pdoMock;
    private $strategyMock;
    
    public function setUp(){
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->strategyMock = $this->getMockBuilder('Tiny\\Sql\\Interfaces\\ExceptionsHandlingStrategy')
                         ->setMethods(['handle', 'getCollected'])
                         ->getMock();
        $this->batchRunner = new Tiny\DbUnit\SqlRunners\SqlBatchSelfCollectingRunner($this->strategyMock, $this->pdoMock);
    }
    
    public function testCollecting(){
        $exceptionMock = new \Exception('Mock exception');
        $collectesStub = [];
        $this->pdoMock->method('query')
                      ->will($this->throwException($exceptionMock));
        $this->strategyMock->expects($this->once())
                           ->method('handle')
                           ->with($exceptionMock, 0);
        $this->strategyMock->expects($this->once())
                           ->method('getCollected')
                           ->with(true)
                           ->willReturn($collectesStub);
        $this->batchRunner->query(['some bad statement']);
        $this->assertSame($collectesStub, $this->batchRunner->getExceptionsCollected());
    }
}
