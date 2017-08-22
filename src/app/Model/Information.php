<?php
namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

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

class Information extends NotORM {

    protected function getTableName($id) {
        return 'information';
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
    /**
     * 获取用户列表
     */
    public function getListItems($condition, $page, $perpage) {
        return $this->getORM()
            ->select('*')
            ->where('bmbh', $condition['bmbh'])
            ->order('id DESC')
            ->limit(($page - 1) * $perpage, $perpage)
            ->fetchAll();
    }

    public function getListTotal($condition) {
        $total = $this->getORM()
            ->where('bmbh', $condition['bmbh'])
            ->count('id');

        return intval($total);
    }

    public function getByArchiveNum($archiveNum)
    {
        return $this->getORM()
            ->where('archive_num=:archive_num', array(':archive_num' => $archiveNum))
            ->fetchOne();

    }

    public function updateByArchiveNum($archiveNum, $data)
    {
        return $this->getORM()
            ->where('archive_num', $archiveNum)
            ->update($data);
    }

}
