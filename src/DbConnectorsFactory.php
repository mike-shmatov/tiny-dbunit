<?php
namespace Tiny\DbUnit;

class DbConnectorsFactory implements Interfaces\ConnectorsFactory
{
    public function makeInMemoryConnector(){
        return new SqliteInMemoryDbConnector();
    }
}