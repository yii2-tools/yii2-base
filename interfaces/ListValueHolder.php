<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 09.02.16 18:08
 */

namespace yii\tools\interfaces;

/**
 * Interface for data objects which supports [[app\models\ListValue]] as their value
 * Interface ListValueHolder
 * @package yii\tools\interfaces
 */
interface ListValueHolder
{
    /**
     * Returns all available values for object as array
     *
     * [
     *     id => value
     * ]
     *
     * where `id` is actual stored value, `value` is user-like description for interface
     *
     * Usage example:
     * – checks in list Formatter
     * – making dropdown list input in forms
     *
     * @return array|null
     */
    public function getListValuesArray();

    /**
     * @return mixed
     */
    public function getCurrentListValue();

    /**
     * @param $value
     * @return mixed
     */
    public function setCurrentListValue($value);

    /**
     * @param $value
     * @return mixed
     */
    public function listValueExists($value);

    /**
     * Returns object attribute which contains current list value of ListValueHolder
     * It will be used like this: $listValue->value, where 'value' is listValueAttribute() result
     * @return mixed
     */
    public function listValueAttribute();
}
