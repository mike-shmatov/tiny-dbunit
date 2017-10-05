<?php
namespace Tiny\DbUnit;

class SqlRunnerSingletonDecorator extends SqlRunner
{
    private static $me;
    
    public static function getInstance(){
        if(!isset(self::$me)){
            self::$me = new static (\Tiny\Sql\Parsers\StatementsSplitter::make(), new \Tiny\DbUnit\SqlBatchSelfCollectingRunner(new \Tiny\DbUnit\PdoExceptionsCollectingHandler()));
        }
        return self::$me;
    }
}
