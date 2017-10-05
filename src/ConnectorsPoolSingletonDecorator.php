<?php
namespace Tiny\DbUnit;

class ConnectorsPoolSingletonDecorator extends ConnectorsPool
{
    private static $me;
    
    public static function getInstance(){
        if(!isset(self::$me)){
            self::$me = new static (new \Tiny\DbUnit\DbConnectorsFactory());
        }
        return self::$me;
    }
}
