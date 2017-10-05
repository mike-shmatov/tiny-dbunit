<?php
namespace Tiny\DbUnit;

class SqlRunner
{
    private $splitter;
    private $batchRunner;
    
    public function __construct($splittingParser, $batchRunner) {
        $this->splitter = $splittingParser;
        $this->batchRunner = $batchRunner;
    }
    
    public function run($pdo, $sql){
        if(file_exists($sql)){
            $sql = file_get_contents($sql);
        }
        $statements = $this->splitter->parse($sql);
        $this->batchRunner->setPdo($pdo);
        $this->batchRunner->query($statements);
    }
    
    public function getExceptionsCollected(){
        return $this->batchRunner->getExceptionsCollected();
    }
}
