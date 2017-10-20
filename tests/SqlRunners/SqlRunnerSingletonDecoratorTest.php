<?php
class SqlRunnerSingletonDecoratorTest extends PHPUnit_Framework_TestCase
{
    public function testIsSingleton(){
        $this->assertSame(\Tiny\DbUnit\SqlRunners\SqlRunnerSingletonDecorator::getInstance(), \Tiny\DbUnit\SqlRunners\SqlRunnerSingletonDecorator::getInstance());
    }
}
