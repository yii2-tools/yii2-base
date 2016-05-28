<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 28.02.16 2:14
 */

namespace yii\tools\behaviors;

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\base\InvalidConfigException;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\tools\interfaces\ParamsHolder;

/**
 * Class CountableBehavior
 *
 * Change global entity number for application instance after owner's insert/delete operation
 * This behavior is designed to use with ActiveParams system
 *
 * Usage examples:
 *
 * 1. Specifying active param name:
 *
 * ```
 * [
 *     'countable' => [
 *         'class' => CountableBehavior::className(),
 *         'counterOwner' => Yii::$app->getModule('users'),
 *         'counterParam' => 'users_count',
 *     ],
 * ]
 * ```
 *
 * Equivalent statements:
 *
 * ```
 * after insert: $owner = ...; $owner->params['users_count'] += 1;
 * after delete: $owner = ...; $owner->params['users_count'] -= 1;
 * ```
 *
 * @package yii\tools\behaviors
 */
class CountableBehavior extends Behavior
{
    /** @var ParamsHolder|Object */
    public $counterOwner;

    /** @var string */
    public $counterParam;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->counterOwner) || (!is_array($this->counterOwner) && !is_object($this->counterOwner))) {
            throw new InvalidConfigException('counterOwner must be configured as array or object');
        }

        if (!isset($this->counterParam)) {
            $this->counterParam = Inflector::camel2id(
                StringHelper::basename(get_class($this->counterOwner))
            ) . '_count';
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => [$this, 'counterInc'],
            ActiveRecord::EVENT_AFTER_DELETE => [$this, 'counterDec']
        ];
    }

    /**
     * Return count of owner entity instances within current application
     * @return int
     */
    public function count()
    {
        if ($this->counterOwner instanceof ParamsHolder) {
            return $this->counterOwner->params[$this->counterParam];
        } else {
            return ArrayHelper::getValue($this->counterOwner, $this->counterParam, 0);
        }
    }

    /**
     * Increment counter
     * @return void
     */
    public function counterInc()
    {
        $this->changeCounterValue(1);
    }

    /**
     * Decrement counter
     * @return void
     */
    public function counterDec()
    {
        $this->changeCounterValue(-1);
    }

    /**
     * @param $value
     */
    protected function changeCounterValue($value)
    {
        if (is_object($this->counterOwner)) {
            if ($this->counterOwner instanceof ParamsHolder) {
                $this->counterOwner->params[$this->counterParam] += $value;
            } else {
                $this->counterOwner->{$this->counterParam} += $value;
            }
        } else {
            $this->counterOwner[$this->counterParam] += $value;
        }
    }
}
