<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 26.02.16 10:32
 */

namespace yii\tools\interfaces;

/**
 * Interface ParamsHolder
 * @package yii\tools\interfaces
 */
interface ParamsHolder
{
    /**
     * Return id of holder in his context
     *
     * @return string
     */
    public function getId();

    /**
     * Return holder's unique Id in application among all objects
     *
     * @return string
     */
    public function getUniqueId();
}
