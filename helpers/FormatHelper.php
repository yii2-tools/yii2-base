<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 09.02.16 13:54
 */

namespace yii\tools\helpers;

use yii\base\InvalidParamException;

/**
 * All available field formats for formatted database records
 * @package yii\tools\helpers
 */
class FormatHelper
{
    const TYPE_STRING   = 'string';
    const TYPE_NUMBER   = 'number';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_LIST     = 'list';
    const TYPE_DATE     = 'date';

    private static $formats = [
        // Type => Formatter's format method
        self::TYPE_STRING   => 'raw',
        self::TYPE_NUMBER   => 'intVal',
        self::TYPE_BOOLEAN  => 'numericBoolean',
        self::TYPE_LIST     => 'raw',
        self::TYPE_DATE     => 'timestamp'
    ];

    public static function typeToFormat($type)
    {
        if (!array_key_exists($type, self::$formats)) {
            throw new InvalidParamException("Invalid type '$type'");
        }

        return self::$formats[$type];
    }
}
