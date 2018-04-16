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

use Bitendian\TBP\UI\Interfaces\RenderInterface as RenderInterface;

/**
 * Class to be extended by modules that needs to render views.
 *
 * This class should not be extended outside TBP. Abstract classes for modules that extends this class are
 * provided to be used on apps.
 *
 * Render methods relays on toString core method. Render implementors must return view code (i.e. HTML) on render
 * method. In AbstractRenderizable extension classes toString is overwritten to get render result. This means classes
 * that extends AbstractRenderizable can be treated as strings (where string is view code returned by render method).
*/
abstract class AbstractRenderizable implements RenderInterface
{
    private $rendered_html = null;

    public function __toString()
    {
        if ($this->rendered_html === null) {
            if (\ob_start()) {
                echo $this->render();
                $this->rendered_html = \ob_get_contents();
                \ob_end_clean();
            }
        }

        return $this->rendered_html;
    }
}
