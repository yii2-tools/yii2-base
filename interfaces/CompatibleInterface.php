<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 13.03.16 18:35
 */

namespace yii\tools\interfaces;

/**
 * Interface for object what can be compatible/not compatible with current environment (i.e. engine version)
 * @package yii\tools\interfaces
 */
interface CompatibleInterface
{
    /**
     * Checks if this object compatible with current environment or not
     * @return string
     */
    public function isCompatible();
}
