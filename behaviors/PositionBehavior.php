<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 10.04.16 19:05
 */

namespace yii\tools\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;

/**
 * Class PositionBehavior
 *
 * @property ActiveRecordInterface $owner
 * @package yii\behaviors
 */
class PositionBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive position value.
     */
    public $positionAttribute = 'position';

    /**
     * @inheritdoc
     *
     * In case, when the value is `null`,
     * value of position attribute of last entity in database will be used.
     */
    public $value;

    /**
     * Value used in insert operation if owner is the first entity instance.
     * Means that position of numeration starts with this value (0 as default).
     *
     * @var int
     */
    public $defaultValue = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->positionAttribute,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return array_merge(parent::events(), [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'shiftPositions'
        ]);
    }

    /**
     * Changes position of owner among the entities of the same type.
     *
     * @param int $position
     * @return boolean
     */
    public function setPosition($position)
    {
        $oldPosition = $this->owner->{$this->positionAttribute};
        $transaction = $this->owner->getDb()->beginTransaction();
        try {
            $isUp = ($position - $oldPosition) > 0;
            $counters = [$this->positionAttribute => $isUp ? -1 : 1];
            $condition = [
                'between',
                $this->positionAttribute,
                $isUp ? $oldPosition : $position,
                $isUp ? $position : $oldPosition,
            ];
            $this->owner->updateAllCounters($counters, $condition);

            $this->owner->{$this->positionAttribute} = $position;
            $this->owner->save();

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);

            return false;
        }
    }

    /**
     * Shifting positions of all entities with the same class (maintaining of sequential numbering).
     */
    public function shiftPositions()
    {
        $this->owner->updateAllCounters(
            [$this->positionAttribute => -1],
            ['>', $this->positionAttribute, $this->owner->{$this->positionAttribute}]
        );
    }

    /**
     * @inheritdoc
     *
     * In case, when the [[value]] is `null`, position of last created entity or default value
     * will be used as value.
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            if (!$this->owner instanceof ActiveRecordInterface) {
                throw new \LogicException('Owner should be instanceof ActiveRecordInterface');
            }

            if (!($last = $this->owner->find()->orderBy([$this->positionAttribute => SORT_DESC])->one())) {
                return $this->defaultValue;
            }

            return $last->{$this->positionAttribute} + 1;
        }

        return parent::getValue($event);
    }
}
