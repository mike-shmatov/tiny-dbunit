<?php
namespace Tiny\DbUnit\ConnectionManagement;

class ConnectorsPoolSingletonDecorator extends ConnectorsPool
{
    private static $me;
    
    public static function getInstance(){
        if(!isset(self::$me)){
            self::$me = new static (new \Tiny\DbUnit\ConnectionManagement\DbConnectorsFactory());
        }
        return self::$me;
    }
}
