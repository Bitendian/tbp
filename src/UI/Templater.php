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

/*
 * Class to templatize.
 *
 * Templates can use a collection of marks to be replaced by Templater.
 *
 * @@VALUE@@ will be replaced by a property called 'value' on context. @@PROPERTY.VALUE@@ will be replaced by a property
 * called 'value' of an object called 'property' on context or by a position called 'value' on an associative array
 * called 'property' on context. If value is a number in @@PROPERTY.VALUE@@ clause will make a replacement with an array
 * value on an indexed array. This construction can be nested, so @@PROP1.PROP2.PROP3.VALUE@@ is a valid clause.
 *
 * @@@VALUES@@@ will be replaced by a concatenation of all values of an array called 'values' on context. Object or
 * array constructions are valid, so @@@PROPERY.VALUES@@@ is a valid clause that means "concatenate all values of an
 * array called 'values' on an object (or array) called PROPERTY on context". This clause can be nested too.
 *
 * ##TEXT## will be replace by result of calling gettext with TEXT as param.
 *
 * Precedence is:
 *
 * - @@ and @@@ will be replaced on first stage
 * - ## will be replaced on a second stage
 *
 * ##@@VALUE@@## is a valid clause, with Templater precedence that means "first replace with the value on context and
 * then make a translation of this replacement". This is valid for ##@@PROPERTY.VALUE@@## as well.
*/

class Templater extends AbstractRenderizable
{
    private $source;
    private $context;
    private $result;

    const SEPARATOR = '@@';
    const ARRAY_SEPARATOR = '@@@';
    const GETTEXT_SEPARATOR = '##';

    public function __construct($source, $context = null)
    {
        $this->source = $source;
        if (is_object($context)) {
            $this->context = clone($context);
        } else {
            $this->context = $context;
        }
    }

    public function render()
    {
        $this->result = $this->loadContent();
        $context = $this->context;
        if (!is_array($context)) {
            $this->context = array();
            $this->context[] = $context;
        }
        $this->replace();

        return $this->result;
    }

    private function loadContent()
    {
        if ($this->source === null) {
            return '';
        }

        return file_get_contents($this->source);
    }

    protected function replace()
    {
        $this->replaceGettext();
        if ($this->context !== null) {
            $this->replaceArrayTags();
            $this->replaceTags();
        }
    }

    protected function replaceGettext()
    {
        while (preg_match(self::getGettextRegexp(), $this->result, $groups) > 0) {
            $key = $groups[1];
            $value = gettext($key);
            $this->result = str_replace($groups[0], $value, $this->result);
        }
    }

    private static function rReplaceProperty($context, $property, &$value)
    {
        // get context properties in lowercase
        if (isset($context) && is_object($context)) {
            $context_vars = array_change_key_case(get_object_vars($context));
        } elseif (isset($context) && is_array($context)) {
            $context_vars = array_change_key_case($context);
        } else {
            return false;
        }

        if ($p = strpos($property, '.')) {
            // must replace with subobject property or associative subarray value
            $property1 = substr($property, 0, $p);
            $property2 = substr($property, $p + 1);
            // if exists property1 as context var $property1 is context and property2 is property
            if (array_key_exists($property1, $context_vars)) {
                return self::rReplaceProperty($context_vars[$property1], $property2, $value);
            }
        } elseif (array_key_exists($property, $context_vars)) {
            // is context property
            if (!is_array($value) && is_object($context_vars[$property])) {
                $value = $context_vars[$property]->__toString();
                return true;
            } elseif (is_array($value) && is_object($context_vars[$property])) {
                return false;
            } elseif (!is_array($value) && is_array($context_vars[$property])) {
                return false;
            } elseif (is_array($value) && is_array($context_vars[$property])) {
                // if property is an array and we expect an array is matching
                foreach ($context_vars[$property] as $item) {
                    if (is_object($item)) {
                        $value[] = $item->__toString();
                    } else {
                        $value[] = $item;
                    }
                }

                return true;
            }

            if (is_array($value)) {
                return false;
            }

            $value = $context_vars[$property];
            return true;
        }

        return false;
    }

    protected function replaceTags()
    {
        while (preg_match(self::getTagsRegexp(), $this->result, $groups) > 0) {
            $property = strtolower($groups[1]);
            $value = null;
            foreach ($this->context as &$context) {
                if (self::rReplaceProperty($context, $property, $value)) {
                    break;
                }
            }

            $this->result = str_replace($groups[0], $value, $this->result);
        }
    }

    protected function replaceArrayTags()
    {
        while (preg_match(self::getArrayTagsRegexp(), $this->result, $groups) > 0) {
            $property = strtolower($groups[1]);
            $value = array();
            foreach ($this->context as $context) {
                if (self::rReplaceProperty($context, $property, $value, true)) {
                    break;
                }
            }

            $this->result = str_replace($groups[0], implode($value), $this->result);
        }
    }

    private static function getArrayTagsRegexp()
    {
        return '/' . self::ARRAY_SEPARATOR . '(.+?)' . self::ARRAY_SEPARATOR . '/';
    }

    private static function getTagsRegexp()
    {
        return '/' . self::SEPARATOR . '(.+?)' . self::SEPARATOR . '/';
    }

    private static function getGettextRegexp()
    {
        return '/' . self::GETTEXT_SEPARATOR . '(.+?)' . self::GETTEXT_SEPARATOR . '/';
    }

    public function getTags()
    {
        $content = $this->loadContent();
        $tags = array();

        // remove array tags (dirty style)
        while (preg_match(self::getArrayTagsRegexp(), $content, $groups) > 0) {
            $tags []= strtolower($groups[1]);
            $content = str_replace($groups[0], '', $content);
        }

        // pull tags
        while (preg_match(self::getTagsRegexp(), $content, $groups) > 0) {
            $tags []= strtolower($groups[1]);
            $content = str_replace($groups[0], '', $content);
        }

        return $tags;
    }

    public function getGettextTags()
    {
        $content = $this->loadContent();
        $tags = array();

        // pull tags
        while (preg_match($this->getGettextRegexp(), $content, $groups) > 0) {
            $tags []= $groups[1];
            $content = str_replace($groups[0], '', $content);
        }

        return $tags;
    }

    public static function url($string, $context = null)
    {
        if ($context == null) return $string;

        while (preg_match(self::getTagsRegexp(), $string, $groups) > 0) {
            $property = strtolower($groups[1]);
            $value = null;
            if (static::rReplaceProperty($context, $property, $value)) {
                break;
            }

            $string = str_replace($groups[0], $value, $string);
        }

        return $string;
    }
}
