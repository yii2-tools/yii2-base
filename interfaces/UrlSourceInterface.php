<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 04.04.16 6:58
 */

namespace yii\tools\interfaces;

/**
 * Interface UrlSourceInterface suited for classes what maintain 3 fields like: url_to, is_route, params [, label]
 * This fields used by [[Url::to]] to establish real Url relation for owner
 *
 * @package yii\tools\interfaces
 */
interface UrlSourceInterface
{
    /**
     * Returns label suited for link to this concrete entity instance
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns URL to this concrete entity instance
     * This method uses [[Url::to]] behavior or similar
     *
     * @return string
     * @see \yii\helpers\Url
     */
    public function getUrl();

    /**
     * Returns query params for maintained URL
     *
     * @return array
     */
    public function getUrlParams();

    /**
     * Returns array if URL for concrete entity instance present as route to site action,
     * or string if URL points to external resource
     *
     * Requirements:
     * – This method should be compatible with [[Url::to]] first parameter
     *
     * @return array|string
     * @see \yii\helpers\Url
     */
    public function getUrlSource();
}
