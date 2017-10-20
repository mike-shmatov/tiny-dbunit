<?php
namespace Tiny\DbUnit\ConnectionManagement;

class SqliteInMemoryDbConnector implements \Tiny\DbUnit\Interfaces\DbConnector
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