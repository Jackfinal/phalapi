<?php
/**
 *
 *
 * @Date   : 17/8/16 14:27
 * @author :WR.dong <wangrd@tcl.com>
 */

namespace App\Common;

use PDO;

class NotORM extends \PhalApi\Database\NotORMDatabase
{


    /**
     * 针对MySQL的PDO链接，如果需要采用其他数据库，可重载此函数
     * @param array $dbCfg 数据库配置
     * @return PDO
     */
    protected function createPDOBy($dbCfg) {
        if (isset($dbCfg['dsn']) && $dbCfg['dsn']) {
            $dsn = $dbCfg['dsn'];
        } else {
            $dsn = sprintf('mysql:dbname=%s;host=%s;port=%d',
                $dbCfg['name'],
                isset($dbCfg['host']) ? $dbCfg['host'] : 'localhost',
                isset($dbCfg['port']) ? $dbCfg['port'] : 3306
            );
        }
        $charset = isset($dbCfg['charset']) ? $dbCfg['charset'] : 'UTF8';
        $pdo = new PDO(
            $dsn,
            $dbCfg['user'],
            $dbCfg['password']
        );
        $pdo->exec("SET NAMES '{$charset}'");

        return $pdo;
    }
}