<?php

/**
 * Author: Pavel Petrov <itnelo@gmail.com>
 * Date: 08.04.16 17:55
 */

namespace yii\tools\interfaces;

/**
 * Interface BackupInterface
 * @package yii\tools\interfaces
 */
interface BackupInterface
{
    /**
     * Create backup for entity.
     *
     * @param bool $force
     */
    public function backup($force = false);

    /**
     * Restore entity data from backup.
     */
    public function backupRestore();

    /**
     * Clear entities backup data.
     */
    public function backupClear();
}
