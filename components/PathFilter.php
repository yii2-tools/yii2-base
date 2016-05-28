<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 05.04.16 14:36
 */

namespace yii\tools\components;

use Yii;
use yii\helpers\VarDumper;
use yii\base\Component;
use yii\caching\ArrayCache;

/**
 * Class PathFilter
 * @package yii\tools\components
 */
class PathFilter extends Component
{
    /**
     * @var ArrayCache request local cache
     */
    public $cache;

    /**
     * @inheritdoc
     */
    public function __construct(ArrayCache $cache, $config = [])
    {
        $this->cache = $cache;
        parent::__construct($config);
    }

    /**
     * Alias for filter() method with standard filter behavior (non-reverse result).
     *
     * @param array $fileNames
     * @param array $except
     * @return array
     */
    public function remained(array $fileNames, array $except = [])
    {
        return $this->filter($fileNames, $except);
    }

    /**
     * Alias for filter() method with reversive behavior (returns excluded records instead of valid).
     *
     * @param array $fileNames
     * @param array $except
     * @return array
     */
    public function excepted(array $fileNames, array $except = [])
    {
        return $this->filter($fileNames, $except, true);
    }

    /**
     * Returns array of file names without restricted (by except rules) paths.
     * This filter based on stripos and performs char-to-char comparation
     *
     * Note: this method doesn't performs any security or content checks.
     *
     * @param array $fileNames
     * @param array $except
     * @param bool $reverseResult when method should produce array of excluded records instead of valid
     * @return array file names in $sourceDir which can be safely used for design pack content
     */
    public function filter(array $fileNames, array $except = [], $reverseResult = false)
    {
        Yii::trace('Filtering files in context of design pack'
            . ' (reverse = ' . VarDumper::dumpAsString($reverseResult) . ')'
            . PHP_EOL . VarDumper::dumpAsString($fileNames), __METHOD__);

        // Local (per request) cache using.
        if (($result = $this->cache->get([__METHOD__, func_get_args()])) !== false) {
            return $result;
        }

        $result = [];

        foreach ($fileNames as $fileName) {
            if ($this->match($fileName, $except)) {
                $result[] = $fileName;
            }
        }

        Yii::info('Filter result set (reverse = ' . VarDumper::dumpAsString($reverseResult) . ')'
            . PHP_EOL . VarDumper::dumpAsString($result), __METHOD__);

        $this->cache->set([__METHOD__, func_get_args()], $result);

        return $result;
    }

    /**
     * Returns callable which can be used by third-party code
     * for determining what some file should be excluded from result set.
     *
     * Callable signature support $path parameter:
     *
     * ```
     * function ($path) {
     *     ...
     * }
     * ```
     *
     * @param array $except
     * @return callable
     */
    public function buildFilterCallback(array $except = [])
    {
        $filter = $this;

        return function ($path) use ($filter, $except) {
            return !$filter->filter([$path], $except, true);
        };
    }

    /**
     * @param string $filepath
     * @param array $except
     * @param bool $reverseResult
     * @return bool
     */
    protected function match($filepath, array $except = [], $reverseResult = false)
    {
        foreach ($except as $exceptedPart) {
            if ($isMatches = (false !== stripos($filepath, $exceptedPart))) {
                Yii::info("File path '$filepath' matches pattern '$exceptedPart'", __METHOD__);
                return !$reverseResult;
            }
        }

        return $reverseResult;
    }
}
