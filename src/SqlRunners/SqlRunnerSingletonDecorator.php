<?php
namespace Tiny\DbUnit\SqlRunners;

class SqlRunnerSingletonDecorator extends SqlRunner
{
    private static $me;
    
    public static function getInstance(){
        if(!isset(self::$me)){
            self::$me = new static (\Tiny\Sql\Parsers\StatementsSplitter::make(), new \Tiny\DbUnit\SqlRunners\SqlBatchSelfCollectingRunner(new \Tiny\DbUnit\SqlRunners\PdoExceptionsCollectingHandler()));
        }
        return self::$me;
    }
}
