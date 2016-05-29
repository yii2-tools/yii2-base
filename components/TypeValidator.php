<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 09.02.16 12:16
 */

namespace yii\tools\components;

use Yii;
use yii\base\NotSupportedException;
use yii\validators\Validator as BaseValidator;
use yii\tools\interfaces\ListValueHolder;
use yii\tools\helpers\FormatHelper;

/**
 * Validator for types: string, number, list, boolean, date
 * Class TypeValidator
 * @package yii\tools\components
 */
class TypeValidator extends BaseValidator
{
    /**
     * @var string
     */
    public $typeAttribute = 'type';

    /**
     * For out-of-model context checks
     * @var string
     */
    public $type = '';

    /**
     * @var mixed
     */
    protected $filteredAttribute = null;

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        Yii::trace("Type validation for attribute '$attribute' started", __METHOD__);

        $this->type = strtolower(trim($model->{$this->typeAttribute}));
        $this->validateAttributeInternal($model, $attribute);
    }

    /**
     * Model validation context
     * @param $value
     */
    protected function validateAttributeInternal($model, $attribute)
    {
        $value = $model->{$attribute};

        $result = ($this->type == FormatHelper::TYPE_LIST)
            ? $this->validateListValue($model, $value)
            : $this->validateValue($value);

        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);

            return;
        }

        Yii::info('Type validation success'
            . PHP_EOL . "Result value for '$attribute': {$this->filteredAttribute}", __METHOD__);
        $model->{$attribute} = $this->filteredAttribute;
    }

    protected function validateListValue(ListValueHolder $model, $value)
    {
        if (!$model instanceof ListValueHolder) {
            throw new NotSupportedException($model::className()
                . " must have implemented ListValueHolder interface for maintaining '{$this->type}' type");
        }

        if (!$model->listValueExists($value)) {
            return $this->buildMessage($this->type);
        }

        $this->filteredAttribute = $value;
    }

    /** @inheridoc */
    protected function validateValue($value)
    {
        if ($this->type == FormatHelper::TYPE_LIST) {
            throw new NotSupportedException("TypeValidator not support '{$this->type}' validation"
                . ' out of model context');
        }

        try {
            $format = FormatHelper::typeToFormat($this->type);
            $this->filteredAttribute = Yii::$app->getFormatter()->format($value, $format);
        } catch (\Exception $e) {
            Yii::warning('Type validation error' . PHP_EOL . $e->__toString(), __METHOD__);

            return $this->buildMessage($this->type);
        }
    }

    protected function buildMessage($type)
    {
        return [Yii::t('errors', 'Invalid value for type "{0}"'), [Yii::t('app', $type)]];
    }
}
