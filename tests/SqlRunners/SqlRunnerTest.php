<?php
class SqlRunnerTest extends PHPUnit_Framework_TestCase
{
    private $rootDirectory;
    private $runner;
    private $batcherMock;
    private $parserMock;
    private $pdoStub;
    
    public function setUp(){
        $this->configureFilesystemMocking();
        $this->parserMock = $this->createMock(\Tiny\Sql\Parsers\StatementsSplitter::class);
        $this->batcherMock = $this->createMock(\Tiny\DbUnit\SqlRunners\SqlBatchRunner::class);
        $this->runner = new \Tiny\DbUnit\SqlRunners\SqlRunner($this->parserMock, $this->batcherMock);
        $this->pdoStub = $this->createMock(\PDO::class);
    }
    
        private function configureFilesystemMocking(){
            $this->rootDirectory = org\bovigo\vfs\vfsStream::setup('root');
            \org\bovigo\vfs\vfsStream::newFile('statements.sql')
                ->at($this->rootDirectory)
                ->setContent('STATEMENT IN FILE;');
        }
    
    public function testRunningFromDirectInput(){
        $sqlString = 'Some sql string';
        $fakeStatements = [
            'first statement',
            'second statemtns'
        ];
        $this->batcherMock->expects($this->once())
                        ->method('setPdo')
                        ->with($this->pdoStub);
        $this->parserMock->expects($this->once())
                   ->method('parse')
                   ->with($sqlString)
                   ->willReturn($fakeStatements);
        $this->batcherMock->expects($this->once())
                        ->method('query')
                        ->with($fakeStatements);
        $this->runner->run($this->pdoStub, $sqlString);
    }
    
    public function testRunningSqlFromFile(){
        $filePath = 'root/statements.sql';
        $fakeStatements = [
            'first statement',
        ];
        $this->parserMock->expects($this->once())
                   ->method('parse')
                   ->with('STATEMENT IN FILE;')
                   ->willReturn($fakeStatements);
        $this->batcherMock->expects($this->once())
                        ->method('query')
                        ->with($fakeStatements);
        $this->runner->run($this->pdoStub, org\bovigo\vfs\vfsStream::url($filePath));
    }
}
