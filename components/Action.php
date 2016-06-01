<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 11.02.16 15:39
 */

namespace yii\tools\components;

use ReflectionClass;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\base\InvalidConfigException;
use yii\base\Action as BaseAction;
use yii\web\NotFoundHttpException;
use yii\db\ActiveQuery as BaseActiveQuery;
use yii\data\Pagination;

/**
 * Class Action
 *
 * Advanced action implementation for application
 * Note: model should implement yii\db\ActiveRecordInterface
 * or finder object with 'findModel' method must be configured (look details in $finder docs below)
 *
 * @package yii\tools\actions
 */
class Action extends BaseAction
{
    // Model instance politics.
    const MODEL_POLICY_NONE     = 'none';       // Model non-required as object instance
    const MODEL_POLICY_REQUIRED = 'required';   // Model as valid object instance required

    // Model search policy.
    const MODEL_SEARCH_POLICY_NONE      = 'none';                       // Search is not important
    const MODEL_SEARCH_POLICY_STRICT    = 'error_if_not_found';         // PHP Error will be generated if not found
    const MODEL_SEARCH_POLICY_COMMON    = 'not_found_state_available';  // yii\web\NotFoundHttpException

    /**
     * Model or array of Models (or model classname string) with whom action have to work
     * If $model is configured as string, will try to create model and load data by $searchKey
     * @var \yii\base\Model|\yii\base\Model[]|string
     */
    public $model;
    /**
     * if true, $model will be treated as array of yii\base\Model
     * In view, model data can be retreived in $model (if false) or $models ([0], [1] ...) variable
     * @var bool
     */
    public $multiple = false;
    /**
     * Key for model setting stage
     * Note, what this may be not a primary key but simple attribute value for WHERE condition,
     * and $model property become array of models
     * @var string  Where condition key
     */
    public $searchKey = 'id';
    /**
     * @var string  Request param name
     */
    public $requestKey;
    /**
     * When the algorithm of model search should use additional "offset" and "limit" params
     * received from Pagination instance (if it specified as "true" Pagination will be created via DI container).
     * If search logic configured as Finder method execution, additional param $pagination will be present.
     * @var Pagination|bool true, false or object
     */
    public $pagination = false;
    /**
     * If model implements \yii\db\ActiveRecordInterface, this setting points what
     * if searchKey/searchValue undefined, fallback search by primaryKey will be triggered
     * @var bool
     */
    public $searchFallback = true;
    /**
     * If configured, findModel method will be called in setModel() stage
     *
     * ```
     * findModel ($searchValue) {
     *     return new Model();
     * }
     * ```
     *
     *              OR
     *
     * ```
     * findModel ($searchKey, $searchValue) {
     *     return new Model();
     * }
     * ```
     *
     *              OR
     *
     * ```
     * findModel ($searchKey, $searchValue, $multiple, $pagination = null) {
     *     return new Model();
     * }
     * ```
     *
     * Where:
     * $searchKey – string, model's search attribute name
     * $searchValue – string, model's search attribute value
     * $multiple – true/false, what kind of result expected: single object or array of objects
     * $pagination - Pagination instance (if configured)
     *
     * @var string
     */
    public $finder;
    /**
     * View path or alias which should be rendered
     * @var string
     */
    public $view;
    /**
     * Params for rendering method
     * @var string
     */
    public $params = [];
    /**
     * User-defined callback for 'prepare' stage of action executing
     * If callback returns false - it means what action shouldn't be executed
     *
     * ```
     * function ($action) {
     *     return true;
     * }
     * ```
     *
     * @var callable
     */
    public $beforeCallback;
    /**
     * User-defined callback that will be executed before rendering stage.
     * Signature equivalent to $beforeCallback except
     * return value of this callback will be ignored.
     * @var callable
     */
    public $beforeRenderCallback;
    /**
     * User-defined callback for 'finishing' stage of action executing
     *
     * ```
     * function ($action) {
     *     return $action->response;
     * }
     * ```
     *
     * @var callable
     */
    public $afterCallback;
    /**
     * Response can be \yii\web\Response instance or other type with implemented __toString()
     * @var mixed
     */
    public $response;
    /**
     * Internal property, used for setModel() asserts in case if model undefined
     * or not properly configured by search criterias
     * default to 'none', means "model instance not required for this action"
     * @var
     */
    public $modelPolicy = self::MODEL_POLICY_NONE;
    /**
     * Internal property, used for setModel() asserts in case if model object defined but data not found in database
     * default to 'required', means "model data search required for this action"
     * @var
     */
    public $modelSearchPolicy = self::MODEL_SEARCH_POLICY_COMMON;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->view)) {
            $this->view = $this->id;
        }

        if (!isset($this->requestKey)) {
            $this->requestKey = $this->searchKey;
        }

        if ($this->modelPolicy == static::MODEL_POLICY_NONE) {
            $this->modelSearchPolicy = static::MODEL_SEARCH_POLICY_NONE;
        }

        if (is_string($this->finder)) {
            $this->finder = Yii::createObject(['class' => $this->finder]);
        }

        if (true === $this->pagination) {
            $this->pagination = Yii::$container->get(Pagination::className());
        }
    }

    /**
     * Basic Run action method implementation
     * @return \yii\web\Response
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    final public function run()
    {
        if (isset($this->response)) {
            Yii::info("Response configured as '" . gettype($this->response) . "': "
                . VarDumper::dumpAsString($this->response), __METHOD__);

            return $this->response;
        }

        if ($response = $this->runInternal()) {
            $this->response = $response;
        }

        if (isset($this->response)) {
            Yii::info('Response exists before rendering stage, action execution completed'
                . PHP_EOL . VarDumper::dumpAsString($this->response, 2), __METHOD__);
        } else {
            if (is_callable($this->beforeRenderCallback)) {
                Yii::info('Executing before render callback', __METHOD__);
                call_user_func($this->beforeRenderCallback, $this);
            }

            $this->render();
        }

        if (is_callable($this->afterCallback)) {
            Yii::info('Executing after callback', __METHOD__);
            $this->response = call_user_func($this->afterCallback, $this);
        }

        return $this->response;
    }

    /**
     * Rendering action
     */
    protected function render()
    {
        Yii::info('Rendering action'
            . PHP_EOL . 'View: ' . $this->view
            . PHP_EOL . 'Params: ' . VarDumper::dumpAsString($this->params, 2), __METHOD__);

        $this->response = $this->controller->render($this->view, $this->params);
    }

    /**
     * Override this method in child classes to customize action logic
     * @return void|mixed
     */
    protected function runInternal()
    {

    }

    /**
     * Creating model using $model configuration property
     * @return bool true, if model was created, false if it has been already configured as object
     * @throws \yii\base\InvalidConfigException
     */
    protected function createModel()
    {
        if ($this->model instanceof \yii\base\Model) {
            Yii::info('Model already configured as instanceof ' . $this->model->className(), __METHOD__);

            return false;
        }

        if (is_array($this->model)) {
            if (!ArrayHelper::isIndexed($this->model) || !$this->model[0] instanceof \yii\base\Model) {
                throw new InvalidConfigException("Property 'model'"
                    . ' must be configured as indexed array of \\yii\\base\\Model');
            }
            Yii::info('Model property configured as array of ' . $this->model[0]->className(), __METHOD__);

            return false;
        }

        if (is_string($this->model)) {
            $this->model = Yii::createObject($configuration = ['class' => $this->model]);
            Yii::info('Model initialized with configuration: ' . VarDumper::dumpAsString($configuration), __METHOD__);
        }

        return true;
    }

    protected function searchModel()
    {
        $searchValue = Yii::$app->getRequest()->get($this->requestKey);
        Yii::info("Searching model data ({$this->searchKey} = $searchValue,"
            . ' multiple = ' . ($this->multiple ? 'true' : 'false') . ')', __METHOD__);

        return $this->model instanceof \yii\db\ActiveRecordInterface
            ? $this->searchModelByActiveQuery($searchValue)
            : $this->searchModelByFinder($searchValue);
    }

    protected function searchModelByFinder($searchValue)
    {
        if (!isset($this->finder) && !is_callable($this->finder) && !is_object($this->finder)) {
            if ($this->modelSearchPolicy == static::MODEL_SEARCH_POLICY_STRICT) {
                throw new InvalidConfigException('Finder must be valid callable'
                    . " or object with implemented 'findModel' method");
            }
            Yii::info("Property 'finder' as callable or object with findModel(\$key, \$value, \$multiple) method"
                . ' is not configured', __METHOD__);

            return false;
        }

        Yii::info('Finder configured as ' . gettype($this->finder) . ', trying to execute', __METHOD__);
        $args = [
            $this->searchKey,
            $searchValue,
            $this->multiple,
            $this->pagination instanceof Pagination ? $this->pagination : null
        ];

        if (is_callable($this->finder)) {
            return $this->model = call_user_func_array($this->finder, $args);
        } elseif (!method_exists($this->finder, 'findModel')) {
            if ($this->modelSearchPolicy == static::MODEL_SEARCH_POLICY_STRICT) {
                throw new InvalidConfigException("Finder object must implement 'findModel' method");
            }
            Yii::warning('Method findModel($key, $value, $multiple) not implemented in Finder object', __METHOD__);

            return false;
        }

        $argsCount = (new ReflectionClass($this->finder))->getMethod('findModel')->getNumberOfParameters();

        return $this->model = call_user_func_array(
            [$this->finder, 'findModel'],
            1 === $argsCount ? [$args[1]] : $args
        );
    }

    protected function searchModelByActiveQuery($searchValue)
    {
        Yii::info('Model implements \yii\db\ActiveRecordInterface', __METHOD__);

        if (empty($searchValue) && $this->searchFallback) {
            $searchValue = Yii::$app->getRequest()->get(
                $this->searchKey = $this->requestKey = $this->model->primaryKey()[0]
            );
            Yii::info('Search value is empty and fallback is on, trying to get ActiveRecord by primary key'
                . " ({$this->searchKey} = $searchValue,"
                . ' multiple = ' . ($this->multiple ? 'true' : 'false') . ')', __METHOD__);
        }

        $query = $this->buildActiveQuery(['=', $this->searchKey, $searchValue]);

        return $this->model = ($this->multiple ? $query->all() : $query->one());
    }

    /**
     * @param string|array $condition
     * @return BaseActiveQuery
     */
    protected function buildActiveQuery($condition)
    {
        /** @var BaseActiveQuery $query */
        $query = $this->model->find()->andWhere($condition);

        if ($this->pagination instanceof Pagination) {
            $query->offset($this->pagination->offset);
            $query->limit($this->pagination->limit);
        }

        return $query;
    }

    /**
     * Ensure model stage
     */
    protected function ensureModel()
    {
        Yii::trace('Model ensuring', __METHOD__);

        if ($this->multiple) {
            if (empty($this->model) || !is_array($this->model)) {
                $this->model = [$this->model];
            }
            foreach ($this->model as $model) {
                $this->ensureModelInternal($model);
            }

            return;
        }

        $this->ensureModelInternal($this->model);
    }

    /**
     * @param $model
     * @throws \yii\base\InvalidConfigException
     */
    protected function ensureModelInternal($model)
    {
        if (!$model instanceof \yii\base\Model) {
            throw new InvalidConfigException("Model must be instanceof \\yii\\base\\Model"
                . ' or a string, represents class name (model required policy)');
        }
    }

    /**
     * @param $result
     * @throws \LogicException
     * @throws \yii\web\NotFoundHttpException
     * @return bool
     */
    protected function ensureModelSearch($result)
    {
        if ($result) {
            return true;
        }
        if ($this->modelSearchPolicy == static::MODEL_SEARCH_POLICY_COMMON) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        } elseif ($this->modelSearchPolicy == static::MODEL_SEARCH_POLICY_STRICT) {
            throw new \LogicException("Model data must be found in storage (strict model search policy)");
        }

        return false;
    }

    protected function configureModel()
    {
        if ($this->modelSearchPolicy !== static::MODEL_SEARCH_POLICY_NONE) {
            $this->ensureModelSearch($this->searchModel());
        }
    }

    /**
     * @inheritdoc
     */
    protected function beforeRun()
    {
        $this->createModel();

        if ($this->modelPolicy == static::MODEL_POLICY_REQUIRED || isset($this->model)) {
            $this->ensureModel();
            Yii::trace('Obtaining model instance for current action', __METHOD__);
            $this->configureModel();
        }

        if (is_callable($this->beforeCallback)) {
            Yii::info('Executing before callback', __METHOD__);
            if (call_user_func($this->beforeCallback, $this) === false) {
                return false;
            }
        }

        $this->params[$this->multiple ? 'models' : 'model'] = isset($this->model) ? $this->model : false;

        Yii::beginProfile("Action '{$this->controller->route}'", __METHOD__);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterRun()
    {
        Yii::endProfile("Action '{$this->controller->route}'", __METHOD__);
    }
}
