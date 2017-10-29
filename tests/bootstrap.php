<?php
$autoloader = require_once realpath('../vendor/autoload.php');
$autoloader->addPsr4('Tiny\\DbUnit\\UnitTests\\', __DIR__);
$autoloader->addPsr4('Tiny\\DbUnit\\', realpath(__DIR__.'/../src/'));

function bootstrapDbSchema(){
        $pool = \Tiny\DbUnit\ConnectionManagement\ConnectorsPoolSingletonDecorator::getInstance();
        $sqliteInMemoryConnector = $pool->getInMemoryConnector();
        $pdo = $sqliteInMemoryConnector->getPdo();
        $sql = 'CREATE TABLE global (field TEXT);';
        $sqlRunner = \Tiny\DbUnit\SqlRunners\SqlRunnerSingletonDecorator::getInstance();
        $sqlRunner->run($pdo, $sql);
    }

bootstrapDbSchema();