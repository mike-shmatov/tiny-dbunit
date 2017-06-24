<?php
namespace Tiny\DbUnit;

class SqliteInMemoryDbConnector implements Interfaces\DbConnector
{
    private $pdo;
    
    public function __construct(){
        $this->pdo = new \PDO('sqlite::memory:');
    }
    
    public function getDbHost() {
        
    }

    public function getDbName() {
        
    }

    public function getDbPassword() {
        
    }

    public function getDbUser() {
        
    }

    public function getPDO() {
        return $this->pdo;
    }

}