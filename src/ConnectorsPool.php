<?php
namespace Tiny\DbUnit;

class ConnectorsPool
{
    private $connectorsFactory;
    private $instances = [];
    
    public function __construct($factory) {
        $this->connectorsFactory = $factory;
    }
    
    public function getInMemoryConnector(){
        if(!isset($this->instances['sqliteInMemory'])){
            $this->instances['sqliteInMemory'] = $this->connectorsFactory->makeInMemoryConnector();
        }
        return $this->instances['sqliteInMemory'];
    }
}
