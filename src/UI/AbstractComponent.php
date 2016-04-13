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

use Bitendian\TBP\UI\Interfaces\ActionInterface as ActionInterface;
use Bitendian\TBP\UI\AbstractWidget as AbstractWidget;

/*
 * Class to extend in order to create Components on apps.
 *
 * Component is a module that has the ability of read and modify models and show views.
 *
 * Action must be called when a Post with action key of a component is detected.
 *
 * Action must be called before any fetch or render. It is responsability of the app ensure (or not) that same instance
 * of the Component is called on action stage and on fetch/render stage.
 *
 * Fetch must be called before any render. App must ensure that same instance of component is called on fetch stage and
 * on render stage. Typical fetch responsability is:
 *
 * - read model and prepare view model for render stage
 * - call nested modules fetch method
 *
 * Render must be called after optional action stage and mandatory fetch stage. Typical render responsability is:
 *
 * - prepare views with provided view models
 * - call nested modules render method
 */

abstract class AbstractComponent extends AbstractWidget implements ActionInterface
{
    private $action;

    public function __construct()
    {
        parent::__construct();
        $this->action = self::actionEncode(get_class($this));
    }

    protected static function actionEncode($action)
    {
        return urlencode(base64_encode($action));
    }

    public static function actionDecode($action)
    {
        return base64_decode(urldecode($action));
    }

    public function runAction(&$params)
    {
        $this->action($params);
    }
}
