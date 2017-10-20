<?php
class SqlBatchRunnerTest extends PHPUnit_Framework_TestCase
{
    private $batchRunner;
    private $pdoMock;
    private $strategyMock;
    
    public function setUp(){
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->batchRunner = $this->getMockBuilder(Tiny\DbUnit\SqlRunners\SqlBatchRunner::class)
                                  ->setConstructorArgs([$this->pdoMock])
                                  ->getMockForAbstractClass();
    }
    
    public function testCreatingWithoutPdo(){
        $caught = NULL;
        try{
            $batchRunner = $this->batchRunner = $this->getMockBuilder(Tiny\DbUnit\SqlRunners\SqlBatchRunner::class)
                                  ->setConstructorArgs([])
                                  ->getMockForAbstractClass();
        } catch (\Exception $ex) {
            $caught = $ex;
        }
        $this->assertNull($caught);
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
