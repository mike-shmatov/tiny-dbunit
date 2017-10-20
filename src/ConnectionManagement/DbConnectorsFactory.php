<?php
namespace Tiny\DbUnit\ConnectionManagement;

class DbConnectorsFactory implements \Tiny\DbUnit\Interfaces\ConnectorsFactory
{
    public function makeInMemoryConnector(){
        return new \Tiny\DbUnit\ConnectionManagement\SqliteInMemoryDbConnector();
    }
}