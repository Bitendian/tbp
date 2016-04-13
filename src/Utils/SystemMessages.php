<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\Utils;

/*
 * Class to manage and centralize messages to be sended to user. Messages are classified into:
 *
 * Info messages
 * Warning messages
 * Error messages
 *
 * Class methods must be static and SystemMessages follows Singleton pattern.
 *
 * Messages are stored under microtime (with microseconds) key to be sorted in chronological order if needed.
 *
 * Messages can be given with a custom id to be identified if needed.
 *
 * SystemMessages provides methods to be saved and restored in order to be shared between executions if needed. If 
 * proper array is provided then that array is used. If no array is provided storing and retrieving relays on SESSION.
 * Once restored, messages are removed from array.
 */

class SystemMessages
{
    protected static $errors = array();
    protected static $warnings = array();
    protected static $infos = array();

    public static function hasMessages()
    {
        return count(self::$errors) + count(self::$warnings) + count(self::$infos);
    }

    public static function hasErrorMessages()
    {
        return count(self::$errors);
    }

    public static function hasWarningMessages()
    {
        return count(self::$warnings);
    }

    public static function hasInfoMessages()
    {
        return count(self::$infos);
    }

    public static function addError($message, $id = '')
    {
        self::$errors[microtime(true)] = array($message, $id);
    }

    public static function addWarning($message, $id = '')
    {
        self::$warnings[microtime(true)] = array($message, $id);
    }

    public static function addInfo($message, $id = '')
    {
        self::$infos[microtime(true)] = array($message, $id);
    }

    public static function getErrors()
    {
        return self::$errors;
    }

    public static function getWarnings()
    {
        return self::$warnings;
    }

    public static function getInfos()
    {
        return self::$infos;
    }

    public static function save(&$array = null)
    {
        if ($array === null) {
            $array = $_SESSION;
        }

        $array['messages_infos'] = serialize(self::$infos);
        $array['messages_warnings'] = serialize(self::$warnings);
        $array['messages_errors'] = serialize(self::$errors);
    }

    public static function restore(&$array = null)
    {
        if ($array === null) {
            $array = $_SESSION;
        }

        if (isset($array['messages_infos'])) {
            self::$infos = unserialize($_SESSION['messages_infos']);
            unset($array['messages_infos']);
        }
        if (isset($array['messages_warnings'])) {
            self::$warnings = unserialize($_SESSION['messages_warnings']);
            unset($array['messages_warnings']);
        }
        if (isset($array['messages_errors'])) {
            self::$errors = unserialize($_SESSION['messages_errors']);
            unset($array['messages_errors']);
        }
    }
}
