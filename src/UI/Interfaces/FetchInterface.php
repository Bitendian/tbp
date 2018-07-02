<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\UI\Interfaces;

/**
 * Interface to be implemented by modules that needs to access model for reading.
 *
 * This interface should not be implemented outside TBP. Abstract classes for modules that implements this interface are
 * provided to be used on apps.
 *
 * Main method is:
 *
 * - fetch
 *
 * Fetch must be called before any render. App must ensure that same instance of component is called on fetch stage and
 * on render stage. Typical fetch responsibility is:
 *
 * - read model and prepare view model for render stage
 * - call nested modules fetch method
 */
interface FetchInterface
{
    public function fetch(&$params);
}
