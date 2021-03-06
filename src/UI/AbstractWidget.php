<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\UI;

use Bitendian\TBP\UI\Interfaces\FetchInterface as FetchInterface;
use Bitendian\TBP\UI\AbstractRenderizable as AbstractRenderizable;

/**
 * Class to extend in order to create Widgets on apps.
 *
 * Widget is a module that has the ability of read models and show views.
 *
 * Fetch must be called before any render. App must ensure that same instance of component is called on fetch stage and
 * on render stage. Typical fetch responsibility is:
 *
 * - read model and prepare view model for render stage
 * - call nested modules fetch method
 *
 * Render must be called after optional action stage and mandatory fetch stage. Typical render responsibility is:
 *
 * - prepare views with provided view models
 * - call nested modules render method
 */
abstract class AbstractWidget extends AbstractRenderizable implements FetchInterface
{
}
