<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 27.02.16 22:20
 */

namespace yii\tools\components;

use Yii;
use yii\helpers\VarDumper;
use yii\caching\ArrayCache as RequestLocalCache;
use yii\db\Command as BaseDbCommand;

/**
 * Class DbCommand
 *
 * Features:
 * – local ArrayCache for queries in current request
 *
 * @package app\components\db
 */
class DbCommand extends BaseDbCommand
{
    /**
     * @var RequestLocalCache
     */
    private static $requestLocalCache = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!static::$requestLocalCache instanceof RequestLocalCache) {
            static::$requestLocalCache = new RequestLocalCache();
        }
    }

    /**
     * Extended functional: caching rawSql data between queryInternal() calls for one query execution
     * @inheritdoc
     */
    public function getRawSql()
    {
        if ($rawSql = static::$requestLocalCache->get('rawSql')) {
            static::$requestLocalCache->delete('rawSql');

            return $rawSql;
        }

        return parent::getRawSql();
    }

    /**
     * Extended functional: flushing request local cache.
     * @inheritdoc
     */
    public function execute()
    {
        static::$requestLocalCache->flush();

        return parent::execute();
    }

    /**
     * Actual query with caching results in local for current request
     * @inheritdoc
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        if ($method !== '') {
            $rawSql = $this->getRawSql();
            $requestLocalCacheKey = implode('', [
                __CLASS__,
                $method,
                $fetchMode,
                $this->db->dsn,
                $this->db->username,
                preg_replace('/\s+/', '', $rawSql)
            ]);
            mb_convert_encoding($requestLocalCacheKey, 'UTF-8', 'UTF-8');
            if (($result = static::$requestLocalCache->get($requestLocalCacheKey)) !== false) {
                Yii::info('Query result served from request local cache'
                    . PHP_EOL . 'Query: ' . VarDumper::dumpAsString($rawSql)
                    . PHP_EOL . 'Result: ' . VarDumper::dumpAsString($result), __METHOD__);

                return $result;
            }
            static::$requestLocalCache->set('rawSql', $rawSql);
        }

        $result = parent::queryInternal($method, $fetchMode);

        if ($method !== '') {
            static::$requestLocalCache->set($requestLocalCacheKey, $result);
        }

        return $result;
    }
}
