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

/*
 * Interface to be implemented by modules that needs to modify model.
 *
 * This interface should not be implemented outside TBP. Abstract classes for modules that implements this interface are
 * provided to be used on apps.
 *
 * Main method is:
 *
 * - action
 *
 * Action must be called when a Post with action key of a component is detected.
 *
 * Action must be called before any fetch or render. It is responsability of the app ensure (or not) that same instance
 * of the Component is called on action stage and on fetch/render stage.
 */

interface ActionInterface
{
    public function action(&$params);
}
