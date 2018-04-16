<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\Domain;

use Bitendian\TBP\Domain\Connection\Cache\RedisCacheConnection;

/**
 * Class AbstractRedisDomain
 * @package Bitendian\TBP\Domain
 */
abstract class AbstractRedisDomain extends AbstractCacheDomain
{
    /**
     * AbstractRedisDomain constructor.
     * @param \stdClass $config
     */
    public function __construct($config)
    {
        $this->connection = new RedisCacheConnection($config);
    }
}
