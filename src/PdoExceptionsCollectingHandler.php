<?php
namespace Tiny\DbUnit;

class PdoExceptionsCollectingHandler
{
    private $collected = [];
    
    public function handle(\Exception $ex){
        if(!is_a($ex, \PDOException::class)){
            throw $ex;
        }
        $this->collected[] = $ex;
    }
    
    public function hasPdoExceptions(){
        return (boolean) count($this->collected);
    }
    
    public function getCollected($clearOut = false) {
        $collected = $this->collected;
        if($clearOut){
            $this->collected = [];
        }
        return $collected;
    }
}
