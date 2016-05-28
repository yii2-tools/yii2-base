<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 11.04.16 12:34
 */

namespace yii\tools\components;

use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;
use yii\tools\components\Action as BaseAction;

/**
 * Class PositionAction
 * @package yii\tools\components
 */
class PositionAction extends BaseAction
{
    /**
     * Key used to determine model instance.
     * @var string
     */
    public $oldPositionKey = 'old';

    /**
     * Key used to determine new position of model.
     * @var string
     */
    public $newPositionKey = 'new';

    /**
     * @var @inheritdoc
     */
    public $modelPolicy = self::MODEL_POLICY_REQUIRED;

    /**
     * @inheritdoc
     */
    protected function searchModel()
    {
        $oldPosition = Yii::$app->getRequest()->getBodyParam($this->oldPositionKey);

        return $this->model = $this->model->find()
            ->andWhere(['=', $this->model->positionAttribute, $oldPosition])
            ->one();
    }

    /**
     * @inheritdoc
     */
    protected function runInternal()
    {
        $newPosition = Yii::$app->getRequest()->getBodyParam($this->newPositionKey);

        if (!is_null($newPosition)) {
            Yii::info('New position (' . $newPosition . ') for model'
                . PHP_EOL . VarDumper::dumpAsString($this->model), __METHOD__);
            $this->model->setPosition($newPosition);
        }

        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = [
            'status' => 1,
            'errors' => [],
            'data' => [],
        ];

        return $response;
    }

    /**
     * @inheritdoc
     */
    protected function ensureModelInternal($model)
    {
        parent::ensureModelInternal($model);

        if (!$model->getBehavior('position')) {
            throw new \LogicException('Model should have position behavior');
        }
    }
}
