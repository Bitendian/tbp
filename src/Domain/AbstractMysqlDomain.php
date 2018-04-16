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

use Bitendian\TBP\Domain\Connection\Database\MysqlDatabaseConnection;

/**
 * Abstract class to be extended by app classes that needs to access a mysql domain.
 */
abstract class AbstractMysqlDomain extends AbstractDatabaseDomain
{
    /**
     * AbstractMysqlDomain constructor.
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->connection = new MysqlDatabaseConnection($config);
    }
}
