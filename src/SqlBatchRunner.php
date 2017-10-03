<?php
namespace Tiny\DbUnit;

class SqlBatchRunner
{
    private $pdo;
    private $exceptionHandler;
    
    public function __construct($pdo, $exceptionHandler){
        $this->pdo = $pdo;
        $this->exceptionHandler = $exceptionHandler;
    }
    
    public function setPdo($pdo){
        $this->pdo = $pdo;
    }
    
    public function query(array $statements){
        foreach($statements as $num => $statement){
            $this->runQuery($statement, $num);
        }
    }
    
        private function runQuery($statement, $num){
            try{
                $this->pdo->query($statement);
            }
            catch (\Exception $ex) {
                $this->exceptionHandler->handle($ex, $num);
            }
        }
}
