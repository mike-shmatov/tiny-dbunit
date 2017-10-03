<?php
class SqlBatchRunnerTest extends PHPUnit_Framework_TestCase
{
    private $batchRunner;
    private $pdoMock;
    private $strategyMock;
    
    public function setUp(){
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->strategyMock = $this->getMockBuilder('Tiny\\Sql\\Interfaces\\ExceptionsHandlingStrategy')
                         ->setMethods(['handle'])
                         ->getMock();
        $this->batchRunner = new Tiny\DbUnit\SqlBatchRunner($this->pdoMock, $this->strategyMock);
    }
    
    public function testCreating(){
        $fakeStatements = [
            'first statement', 'second statement'
        ];
        $this->pdoMock->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(['first statement'], ['second statement']);
        $this->batchRunner->query($fakeStatements);
    }
    
    public function testExceptionsHandlingStrategy(){
        $exceptionMock = new \Exception('Mock exception');
        $this->pdoMock->method('query')
                      ->will($this->throwException($exceptionMock));
        $this->strategyMock->expects($this->once())
                           ->method('handle')
                           ->with($exceptionMock, 0);
        $this->batchRunner->query(['some bad statement']);
    }
    
    /**
     * @test
     */
    public function canChangePdo(){
        $newPdo = $this->createMock(\PDO::class);
        $this->batchRunner->setPdo($newPdo);
        $fakeStatemens = ['first'];
        $newPdo->expects($this->once())
               ->method('query')
               ->with('first');
        $this->pdoMock->expects($this->never())
                      ->method('query');
        $this->batchRunner->query($fakeStatemens);
    }
}
