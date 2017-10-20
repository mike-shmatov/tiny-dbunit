<?php
/**
 * Self-testing approach: current TestCase extends the TestCase being tested.
 */
class PdoExceptionsOnInvalidSqlTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    private $expectedException;
    
    public function setUp(){
        $this->useInMemoryConnector();
        try{
            $this->runSql('THIS IS A BAD SQL STATEMENT; AND THIS IS ANOTHER ONE;');
        } catch (\Exception $ex) {
            $this->expectedException = $ex;
        }
        parent::setUp();
    }
    
    public function tearDown() {
        parent::tearDown();
        $this->expectedException = NULL;
    }
    
    /**
     * Stub
     */
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
    
    public function testSqlExecutionExceptionThrown(){
        $this->assertInstanceOf(\Tiny\DbUnit\Exceptions\SqlExecutionException::class, $this->expectedException);
    }
    
    public function testExceptionContainsRelevantInfo(){
        $this->assertRegExp('/.*near "THIS":.*near "AND":.*/s', $this->expectedException->getMessage());
    }
}