<?php
namespace Tiny\DbUnit;

class ConnectorsPool
{
    private $connectorsFactory;
    private $instances = [];
    
    public function __construct($factory) {
        $this->connectorsFactory = $factory;
    }
    
    public function getInMemoryConnector($id = NULL){
        if(is_null($id)){
            $id = 'sqliteInMemory';
        }
        if($id === true){
            return $this->connectorsFactory->makeInMemoryConnector();
        }
        if(!isset($this->instances[$id])){
            $this->store($id, $this->connectorsFactory->makeInMemoryConnector());
        }
        return $this->instances[$id];
    }
    
    public function store($id, $connector){
        $this->instances[$id] = $connector;
    }
}
