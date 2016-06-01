<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 24.04.16 4:33
 */

namespace yii\tools\interfaces;

use yii\db\ActiveQueryInterface as BaseActiveQueryInterface;

/**
 * A class implementing this interface should also use
 * traits from ActiveQueryInterface description.
 * @package app\interfaces
 */
interface SecureActiveQueryInterface extends BaseActiveQueryInterface
{
    /**
     * Returns secure state of active query.
     * true means that active query raises PopulateEvent with $models
     * which can be modified by SecureBehavior (or other components)
     * in context of per-instance access control.
     * @return bool
     */
    public function isSecure();

    /**
     * Sets up secure state for active query.
     * @param boolean $secure secure status
     */
    public function secure($secure);

    /**
     * Returns attribute field [secure:on|off] in $rows array
     * used to secure checks during populate stage.
     * @return string
     */
    public function getSecureAttribute();

    /**
     * Returns attribute field [secure item name] in $rows array
     * used to secure checks during populate stage.
     * @return string
     */
    public function getSecureItemAttribute();
}
