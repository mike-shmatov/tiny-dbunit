<?php
class EnvironmentTest extends PHPUnit_Extensions_Database_TestCase
{
    private static $connection;
    private static $pdo;
    
    protected function getConnection() {
        if(self::$pdo === NULL){
            $pdo = new \PDO('sqlite::memory:');
            self::$pdo = $pdo;
            $this->initializeDB($pdo);
        }
        if(self::$connection === NULL){
            self::$connection = $this->createDefaultDBConnection(self::$pdo);
        }
        return self::$connection;
    }
    
        private function initializeDB(\PDO $pdo){
            $pdo->query('CREATE TABLE t1(id, c1, c2);');
        }

    protected function getDataSet() {
        $data = [
            't1' => [
                ['id' => 1, 'c1' => '1', 'c2' => '2']
            ]
        ];
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet($data);
    }
    
    public function testTableFilled(){
        $this->assertTableRowCount('t1', 1);
    }
}