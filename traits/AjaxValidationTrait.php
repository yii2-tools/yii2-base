<?php

namespace yii\tools\traits;

use Yii;
use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;

trait AjaxValidationTrait
{
    /**
     * Performs ajax validation
     *
     * @param $model
     * @return \yii\console\Response|\yii\web\Response
     */
    protected function performAjaxValidation($model)
    {
        $result = ($model instanceof Model)
            ? ActiveForm::validate($model)
            : ActiveForm::validateMultiple($model);

        $response = Yii::$app->getResponse();

        if (!empty($result) && $ajaxRedirect = Yii::$app->getSession()->get('site_ajax_redirect')) {
            $response = $response->redirect($ajaxRedirect);
            Yii::$app->getSession()->remove('site_ajax_redirect');
        } else {
            $response->format = Response::FORMAT_JSON;
            $response->data = $result;
        }

        return $response;
    }
}
