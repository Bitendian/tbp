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
 * Interface to be implemented by modules that needs to render views.
 *
 * This interface should not be implemented outside TBP. Abstract classes for modules that implements this interface are
 * provided to be used on apps.
 *
 * Main methods are:
 *
 * - render
 *
 * Render must be called after optional action stage and mandatory fetch stage. Typical render responsability is:
 *
 * - prepare views with provided view models
 * - call nested modules render method
*/
interface RenderInterface
{
    public function render();
}
