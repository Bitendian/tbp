<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitendian\TBP\Domain;

use Bitendian\TBP\Utils\Config as Config;
use Bitendian\TBP\Domain\AbstractDatabaseDomain as AbstractDatabaseDomain;
use Bitendian\TBP\Domain\Connection\Database\MysqlDatabaseConnection as MysqlDatabaseConnection;

/*
 * Abstract class to be extended by app classes that needs to access a mysql domain.
 *
 * Provides two convenience methods to manage select results:
 *
 * - get_single returns a single object (or false) from selects that returns only one value
 * - get_all returns array of objects with all results from selects that returns multiple values
*/

abstract class AbstractMysqlDomain extends AbstractDatabaseDomain
{
    public function __construct($config)
    {
        $this->connection = new MysqlDatabaseConnection($config);
    }
}
