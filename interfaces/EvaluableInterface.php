<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 13.03.16 16:47
 */

namespace yii\tools\interfaces;

/**
 * Interface for object what support evaluating themselves as value
 * @package yii\tools\interfaces
 */
interface EvaluableInterface
{
    /**
     * Evaluate self as value for external use
     * @return string
     */
    public function evaluate();
}
