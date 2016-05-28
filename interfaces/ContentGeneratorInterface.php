<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 21.04.16 6:24
 */

namespace yii\tools\interfaces;

/**
 * Interface ContentGeneratorInterface
 * @package yii\tools\interfaces
 */
interface ContentGeneratorInterface
{
    /**
     * Generates content of current entity.
     *
     * @return mixed
     */
    public function generateContent();
}
