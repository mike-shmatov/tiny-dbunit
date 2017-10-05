<?php
namespace Tiny\DbUnit;

abstract class SqlBatchRunner
{
    private $pdo;
    
    public function __construct($pdo = NULL){
        $this->pdo = $pdo;
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
                static::handle($ex, $num);
            }
        }
        
    abstract protected function handle(\Exception $ex, $num);
}
