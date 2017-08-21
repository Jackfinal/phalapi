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

class Unit extends NotORM {

    protected function getTableName($id) {
        return 'unit';
    }
    
    /**
    * 获得所有的子部门编号
    * @param string $dept_num -1  表示取出所有的部门编号
    * @return array()
    */
    public function getAllChildDeptId($unitNumber,$self=true)
    {
        $unit = $this->getORM();
        if(strlen($unitNumber)<=0){
            return array();
        }
        $unit->select('*')->where('parent_number',$unitNumber);  
        if($self){
            $unit->or('Number',$unitNumber);
        }
        $data = $unit->fetchAll();
        return $data;
    }
    
}
