<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 07.06.16 11:33
 */

namespace yii\tools\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * Class HtmlPurifierBehavior
 * Do not use multiple attributes on $attributes array, they all receive the same value.
 *
 * @package yii\tools\behaviors
 */
class HtmlPurifierBehavior extends AttributeBehavior
{
    /**
     * @var string owner's attribute that contains unsafe code.
     */
    public $htmlAttribute = 'content';

    /**
     * @var array default config for Formatter's asHtml method
     */
    public $defaultConfig = [
        'HTML.SafeObject' => true,
        'Output.FlashCompat' => true,
        'HTML.SafeEmbed' => true,
        'HTML.SafeIframe' => true,
        // allow youtube and vimeo iframes
        'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
    ];

    /**
     * @var array config for Formatter's asHtml method
     */
    public $config = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => $this->htmlAttribute,
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->htmlAttribute,
                BaseActiveRecord::EVENT_AFTER_FIND => $this->htmlAttribute,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        return Yii::$app->getFormatter()->asHtml(
            $this->owner->{$this->htmlAttribute},
            array_merge($this->defaultConfig, $this->config)
        );
    }
}
