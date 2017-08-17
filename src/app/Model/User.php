<?php
namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;
use App\Common\Tools as CommonTools;

/**

CREATE TABLE `phalapi_curd` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `title` varchar(20) DEFAULT NULL,
    `content` text,
    `state` tinyint(4) DEFAULT NULL,
    `post_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

 */

class User extends NotORM {

    protected function getTableName($id) {
        return 'user';
    }
     //通过archive_num获取数据
    /*public static function getDataByArchiveNum($archive_num,$fields=array('*')){
        $data = Yii::app()->db->createCommand()
            ->select($fields)
            ->from(self::$tableName)
            ->where('archive_num=:archive_num',array(':archive_num'=>$archive_num))
            ->queryRow();
        return $data;
    }*/
    public function addInfromation($information)
    {
        return $this->getORM()->insert($information);
        //$id = $user->insert_id();
    }
    public function getListItems($condition, $page, $perpage) {
        return $this->getORM()
            ->select('*')
            ->where('Number', $condition['unit_number'])
            ->order('id DESC')
            ->limit(($page - 1) * $perpage, $perpage)
            ->fetchAll();
    }

    public function getListTotal($condition) {
        $total = $this->getORM()
            ->where('Number', $condition['unit_number'])
            ->count('id');
        return intval($total);
    }
}
