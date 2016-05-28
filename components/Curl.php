<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 19.04.16 2:52
 */

namespace yii\tools\components;

use Yii;
use yii\helpers\VarDumper;
use yii\base\Component;
use yii\tools\interfaces\RequestInterface;

/**
 * Class Params
 * For management component params
 * @package yii\tools\components
 */
class Curl extends Component implements RequestInterface
{
    /**
     * @inheritdoc
     */
    public function request($url, array $params = [])
    {
        Yii::trace("Starting request to URL '$url'"
            . (!empty($params) ? PHP_EOL . 'With params: ' . VarDumper::dumpAsString($params) : ''), __METHOD__);

        if (!($curl = curl_init())) {
            Yii::error('Failed to initialize cURL session: ' . VarDumper::dumpAsString(curl_error($curl)), __METHOD__);
            return false;
        }

        Yii::info('cURL session successfully initialized', __METHOD__);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!empty($params)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($curl);
        curl_close($curl);

        Yii::info('Response received' . PHP_EOL . $response, __METHOD__);

        return $response;
    }
}
