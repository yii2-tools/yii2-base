<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 24.04.16 4:31
 */

namespace yii\tools\events;

use Yii;
use yii\db\ActiveQueryInterface;
use yii\tools\interfaces\SecureActiveQueryInterface;

class PopulateEvent extends ActiveQueryEvent
{
    /**
     * The raw query result from database (array of arrays).
     * @var array
     */
    public $rows;

    /**
     * @inheritdoc
     */
    public function __construct(ActiveQueryInterface $query, array $rows, $config = [])
    {
        $this->rows = $rows;
        parent::__construct($query, $config);
    }

    /**
     * Returns active query secure status (if present).
     * @return bool
     * @see SecureActiveRecord
     */
    public function isSecure()
    {
        if ($this->query instanceof SecureActiveQueryInterface) {
            return $this->query->isSecure();
        }

        return false;
    }
}
