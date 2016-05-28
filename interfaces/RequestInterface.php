<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 19.04.16 3:05
 */

namespace yii\tools\interfaces;

/**
 * Interface RequestInterface
 * @package yii\tools\interfaces
 */
interface RequestInterface
{
    /**
     * Performs request to external server.
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function request($url, array $params = []);
}
