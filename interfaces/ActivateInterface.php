<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 26.03.16 17:37
 */

namespace yii\tools\interfaces;

/**
 * Interface for object what can be activated in current environment
 * @package yii\tools\interfaces
 */
interface ActivateInterface
{
    /**
     * Returns current activation status for object
     * @return bool
     */
    public function isActive();

    /**
     * Performs activation statements for this object
     * @return string
     */
    public function activate();
}
