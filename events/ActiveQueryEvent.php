<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 24.04.16 4:30
 */

namespace yii\tools\events;

use Yii;
use yii\base\Event;
use yii\db\ActiveQueryInterface;

class ActiveQueryEvent extends Event
{
    /**
     * @var ActiveQueryInterface
     */
    public $query;

    /**
     * @inheritdoc
     */
    public function __construct(ActiveQueryInterface $query, $config = [])
    {
        $this->query = $query;
        parent::__construct($config);
    }
}
