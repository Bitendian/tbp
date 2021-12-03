# TBP

*TBP* is a light framework designed to develop small and medium
websites. Focused in component oriented

## Database interfaces

### SQLite
Connector to SQLite3. Works with prepared statements (mandatory). Support for transactions.

#### Installation
PHP extension ```ext-sqlite3``` required. Check ```phpinfo()```. By default, ```tbp-web-server``` Docker image supports SQLite.

Add to your ```composer.json```, at ```require``` section:
```
"ext-sqlite3": "*"
```


#### Configuration
Create a config file, like ```config/my-sqlite.config```
```
# TBP/SQLite config file.
# ALL PARAMETERS ARE OPTIONAL!

# Database file name (if not defined, will use 'default.db')
filename=resources/test.db

# Access type
# readOnly=yes

# Encryption key (if not defined, no encryption used)
# encryptionKey=
```
#### Implementation

Create a *Domain* extending ```AbstractSqliteDomain``` and pass your config file. Add one method for each needed query.
```php
class MyDomain extends AbstractSqliteDomain
{
    public function __construct()
    {
        $configReader = new Config(__CONFIG_PATH__);            // <-- get a config reader, passing your config folder
        $config = $configReader->getConfig('my-sqlite');        // <-- read your configuration, passing your config base name
        $config->filename =                                     // <-- convert your relative path into absolute path
            __BASE_PATH__ .
            DIRECTORY_SEPARATOR .
            $config->filename;
        parent::__construct($config);
        $this->open();                                          // <-- open a db connection and let's rock!
    }

    public function addRegister($a, $b)
    {
        $sql = "INSERT INTO `MyTableWithAutoInc` (`a`, `b`) VALUES (?, ?)";
        $params = array($a, $b);
        return self::insertWithAutoincrement($this->connection->command($sql, $params));
    }

    public function addAnotherRegister($a, $b)
    {
        $sql = "INSERT INTO `MyOtherTable` (`a`, `b`) VALUES (?, ?)";
        $params = array($a, $b);
        return $this->connection->command($sql, $params);
    }

    public function getRegisters($b)
    {
        $sql = "SELECT * FROM `MyTableWithAutoInc` WHERE `b` <= ?";
        $params = array($b);
        return self::getAll($this->connection->select($sql, $params));
    }

    public function getRegisterById($id)
    {
        $sql = "SELECT * FROM `MyTableWithAutoInc` WHERE `MyId` = ?";
        $params = array($id);
        return self::getSingle($this->connection->select($sql, $params));
    }
}
```
