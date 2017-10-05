<?php
class SqlRunnerSingletonDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSingleton(){
        $this->assertSame(\Tiny\DbUnit\SqlRunnerSingletonDecorator::getInstance(), \Tiny\DbUnit\SqlRunnerSingletonDecorator::getInstance());
    }
}
