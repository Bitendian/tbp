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

use Bitendian\TBP\Domain\Connection\Interfaces\CacheConnectionInterface;

/**
 * Abstract class to be extended by app classes that needs to access a cache domain.
 *
 * TBP provides extensions classes for well known cache servers (redis, memcached...)
*/
abstract class AbstractCacheDomain
{
    /**
     * @var CacheConnectionInterface
     */
    protected $connection;
}
