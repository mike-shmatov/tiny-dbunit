# Tiny/DbUnit

## About

This package helps with speeding up database tests making use of SQLite in-memory engine.
It takes care of creating and scoping db connections and helps managing schemas.

## Suggested approaches

Using SQLite in-memory engine makes possible to have multiple databases (and thus 
schemas) at the same time so it makes sense to distinguish globally scoped connection 
and test case scoped connections. Global one would be fine for regular purposes 
when working with relatively stable schemas. And connections limited to specific test
cases are ok as some sort of sandboxing or when schema is expected to be changed frequently
by the code tested.

### Test cases with global connection

Only single global connection is supported by now. Connection's db schema should be 
created in [--bootstrap](https://phpunit.de/manual/5.7/en/textui.html#textui.clioptions) file 
and look like:

```php
// ...
function bootstrapDbSchema(){
    // retrieving instances of connectors' pool and sql statements runner
    $pool = \Tiny\DbUnit\ConnectionManagement\ConnectorsPoolSingletonDecorator::getInstance();
    $sqlRunner = \Tiny\DbUnit\SqlRunners\SqlRunnerSingletonDecorator::getInstance();

    // getting PDO connection
    $sqliteInMemoryConnector = $pool->getInMemoryConnector();
    $pdo = $sqliteInMemoryConnector->getPdo();

    // running schema sql which can be a plain string with all statements or 
    // absolute filepath to the file with SQL instructions
    $sql = 'CREATE TABLE tbl (id INTEGER PRIMARY KEY AUTOINCREMENT, value TEXT);';
    // $sql = realpath(__DIR__.'/schema-sqlite.sql');
    
    $sqlRunner->run($pdo, $sql);
}

bootstrapDbSchema();
// ...
```

The test case would look like:

```php
class GlobalConnectionTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    // this class inherits protected property of $pdo
    // from parent \Tiny\DbUnit\AbstractDbUnitTestCase

    public function setUp(){
        // preparing a connection, please note that it should go first 
        // so parent setUp runs with connection in place
        $this->useInMemoryConnector();
        parent::setUp();
    }

    // please note no getConnection() method defined
    // it is already implemented in  \Tiny\DbUnit\AbstractDbUnitTestCase
    
    // use of PHPUnit's regular functionality to set up the data
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'tbl' => [
                ['id' => 1, 'value' => 'whatever'],
                ['id' => 2, 'value' => 'yet whatever']
            ]
        ]);
    }
    
    public function testRowCount(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('tbl'));
    }
    
    public function testDataSetUp(){
        $this->assertTableContains(
                ['id' => 1, 'value' => 'whatever'], 
                $this->getConnection()->createQueryTable('tbl', 'SELECT * FROM tbl;'));
    }
    
    public function testQueringPdo(){
        $result = $this->pdo->query('SELECT * FROM tbl WHERE id = 2;', \PDO::FETCH_ASSOC);
        $row = $result->fetchAll()[0];
        $this->assertEquals(['id' => 2, 'value' => 'yet whatever'], $row);
    }
}
```

### Test cases with scoped connection

When doing test case scoped connections no special bootstrapping is needed, everything happens
within the test case. The following example presumes there is an SQL file exists on path 
`./sql-files/users.sql` (relatively to test case file) with contents:

```sql
CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, login TEXT, password_hash TEXT);
```

```php
class TestCaseScopeConnectionTest extends \Tiny\DbUnit\AbstractDbUnitTestCase
{
    // setUpBeforeClass is the place to call createTestCaseConnection() method
    public static function setUpBeforeClass() {
        self::createTestCaseConnection();
        self::beforeClassSql(realpath(__DIR__.'/sql-files/users.sql'));
        parent::setUpBeforeClass(); // don't forget parent
    }
    
    protected function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            'users' =>[
                ['id' => 1, 'login' => 'login-1', 'password_hash' => 'abcdefg'],
                ['id' => 2, 'login' => 'login-2', 'password_hash' => 'gfedcba']
            ]
        ]);
    }
    
    public function setUp(){
        // still need to say what connector to use for each test
        $this->useInMemoryConnector();
        parent::setUp();
    }
    
    public function testTableIsFilled(){
        $this->assertEquals(2, $this->getConnection()->getRowCount('users'));
    }
    
    public function testNoOtherTablesExist(){
        // again please note $this->pdo field coming from parent \Tiny\DbUnit\AbstractDbUnitTestCase
        $result = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'  AND name NOT LIKE '%sqlite%';", \PDO::FETCH_ASSOC);
        $tables = array_column($result->fetchAll(), 'name');
        $this->assertCount(1, $tables);
        $this->assertEquals('users', $tables[0]);
    }
}
```

## API provided

### TestCase methods

`\Tiny\DbUnit\AbstractDbUnitTestCase` subclasses PHPUnit's `\PHPUnit_Extensions_Database_TestCase`.
There is no specific testing API added except for connections and schemas configuring purposes.

```php
self::beforeClassSql($sql) // SQL as string or filepath to .sql
```
Used in `setUpBeforeClass()` to run SQL needed to be executed before all tests in current test case.

```php
self::createTestCaseConnection();
```
Used in `setUpBeforeClass()` to make separate connection for current test case only.

```php
self::afterClassSql($sql); // SQL as string or filepath to .sql
```
Used in `tearDownAfterClass()` to run SQL needed to be executed after all tests in current test case.

```php
$this->useInMemoryConnector();
```
Used in `setUp()` to set up each test with connection

```php
$this->runSql($sql); // SQL as string or filepath to .sql 
```
Used in `setUp()`, `tearDown()`, or even any test method to run SQL.

`$sql` arguments given as filepaths can be provided as absolute or relative (to test case) paths to SQL files.
Besides, they can be provided as lists or arrays or a combination. So all examples below are valid:

```php
$this->runSql('../sample-table-schema.sql', './another-table-schema.sql');
$this->runSql(['../sample-table-schema.sql', './another-table-schema.sql']);
$this->runSql('../sample-table-schema.sql', [
    'CREATE TABLE table1 (id INTEGER);',
    './another-table-schema.sql'
]);
```

### Special objects

There are also 2 globally accessible objects:

#### Connectors pool
implemented as singleton

`\Tiny\DbUnit\ConnectionManagement\ConnectorsPoolSingletonDecorator`

`+ static getInstance()`

`+ getInMemoryConnector()` passing no arguments will always return same connector

#### SQL queries runner
implemented as singleton

`\Tiny\DbUnit\SqlRunners\SqlRunnerSingletonDecorator`

`+ static getInstance()`

`+ run($pdo, $sql)` seem to be self-explanatory enough

## Advantages

### Speed
Since SQLite in-memory engine will keep the whole database in memory so no disk operations 
will be involved. At first this might seem no big difference since no huge datasets are usually 
used for testing. But actually each test method is supported by lots of truncating and inserting
to set up and tear down. Using SQLite in-memory engine can really speed up testing process. 

### Flexibility
SQLite in-memory makes possible having multiple connections at the same time. Every database 
related to each connection is distinct from every other. 
That gives a lot of freedom to play around with schemas while developing.

## Downsides

SQLite has some limitations and constraints concerning  SQL abilities and data types. So if application
depends heavily on any features unsupported by SQLite then testing with targeted database seems
to be more appropriate option.

In-memory database's schema is not persisted anywhere and is gone when connection gets closed.
So schema management has to be performed and there seem to exist no options other than having 
SQL's hardcoded somewhere or (better) stored in files. Basically, be ready for no GUI such 
as `phpMyAdmin` to develop and maintain schemas.

SQLite SQL dialect differs a bit from others. So some effort will be needed to port schemas to
target database vendors.

**Also please beware of current limitation to switch easily from SQLite in-memory to any other engine!
This constraint has nothing to do with SQLite itself, it is because current implementation of this 
package has no such abilites.** Still if you decide to use it for db-testing it is highly recommended
to make single (or at least just a few) abstract test case classes to have single place to make 
such changes when needed.

## License

MIT