<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 24.04.16 5:03
 */

namespace yii\tools\components;

use Yii;
use yii\db\ActiveRecord as BaseActiveRecord;

class ActiveRecord extends BaseActiveRecord
{
    /**
     * Suited for ActiveParams using.
     * @var array
     */
    public $params;

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }
}
