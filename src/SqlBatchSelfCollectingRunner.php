<?php
namespace Tiny\DbUnit;

class SqlBatchSelfCollectingRunner extends SqlBatchRunner
{
    private $exceptionHandler;
    
    public function __construct($exceptionHandler, $pdo = NULL){
        $this->exceptionHandler = $exceptionHandler;
        parent::__construct($pdo);
    }
    
    protected function handle(\Exception $ex, $num){
        $this->exceptionHandler->handle($ex, $num);
    }
    
    public function getExceptionsCollected(){
        return $this->exceptionHandler->getCollected(true);
    }
}
