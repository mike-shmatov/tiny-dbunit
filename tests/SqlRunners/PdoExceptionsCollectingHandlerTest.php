<?php
class PdoExceptionsCollectingHandlerTest extends PHPUnit_Framework_TestCase
{
    private $handler;
    
    public function setUp() {
        $this->handler = new \Tiny\DbUnit\SqlRunners\PdoExceptionsCollectingHandler();
    }
    
    public function testGeneralExceptionIsRethrown(){
        $this->expectException(\Exception::class);
        $this->handler->handle(new \Exception());
    }
    
    public function testPdoExceptionNotThrown(){
        $caucht = NULL;
        try{
            $this->handler->handle(new \PDOException());
        }
        catch (Exception $ex) {
            $caucht = $ex;
        }
        $this->assertNull($caucht);
    }
    
    public function testPdoExceptionsGetCollected(){
        $ex1 = new PDOException('ex1');
        $ex2 = new PDOException('ex2');
        $this->handler->handle($ex1);
        $this->handler->handle($ex2);
        $this->assertTrue($this->handler->hasPdoExceptions());
        $this->assertEquals([$ex1, $ex2], $this->handler->getCollected());
    }
    
    /**
     * @test
     */
    public function canGetCollectedMultipleTimes(){
        $ex1 = new PDOException('ex1');
        $ex2 = new PDOException('ex2');
        $this->handler->handle($ex1);
        $this->handler->handle($ex2);
        $this->assertTrue($this->handler->hasPdoExceptions());
        $this->assertEquals([$ex1, $ex2], $this->handler->getCollected());
        $this->assertEquals([$ex1, $ex2], $this->handler->getCollected());
    }
    
    /**
     * @test
     */
    public function canGetCollectedAndClearThemOut(){
        $ex1 = new PDOException('ex1');
        $ex2 = new PDOException('ex2');
        $this->handler->handle($ex1);
        $this->handler->handle($ex2);
        $this->assertTrue($this->handler->hasPdoExceptions());
        $this->assertEquals([$ex1, $ex2], $this->handler->getCollected(true));
        $this->assertFalse($this->handler->hasPdoExceptions());
        $this->assertEquals([], $this->handler->getCollected());
    }
}
