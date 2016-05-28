<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 14.03.16 8:53
 */

namespace yii\tools\interfaces;

/**
 * Interface for object what support holding his owner object
 * $owner is instance of \yii\base\Object means what interface suited to be used in Yii2 framework context only
 * @package yii\tools\interfaces
 */
interface OwnableInterface
{
    /**
     * Returns owner of this object
     * @return \yii\base\Object
     */
    public function getOwner();

    /**
     * Setup owner for this object
     * @param \yii\base\Object $owner
     */
    public function setOwner($owner);
}
