<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 24.04.16 5:39
 */

namespace yii\tools\components;

use Yii;
use yii\db\ActiveQuery as BaseActiveQuery;
use yii\tools\events\PopulateEvent;

class ActiveQuery extends BaseActiveQuery
{
    /**
     * @event Event an event that is triggered before the record is populated with query result.
     */
    const EVENT_BEFORE_POPULATE = 'beforePopulate';

    /**
     * @inheritdoc
     */
    public function populate($rows)
    {
        $event = new PopulateEvent($this, $rows);
        $this->trigger(static::EVENT_BEFORE_POPULATE, $event);
        $rows = $event->rows;

        return parent::populate($rows);
    }
}
