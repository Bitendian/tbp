<?php

namespace Assegura\App\Domain;

use Assegura\App\Domain\Connection\Database\MssqlDatabaseConnection;
use Bitendian\TBP\Domain\AbstractDatabaseDomain;

/*
 * Abstract class to be extended by app classes that needs to access a mssql domain.
 *
 * Provides two convenience methods to manage select results:
 *
 * - get_single returns a single object (or false) from selects that returns only one value
 * - get_all returns array of objects with all results from selects that returns multiple values
*/

class AbstractMssqlDomain extends AbstractDatabaseDomain
{
    /**
     * AbstractMysqlDomain constructor.
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->connection = new MssqlDatabaseConnection($config);
    }
}
