<?php
namespace App\Api;

use PhalApi\Api;

/**
 * 类别管理接口服务类
 *
 */

class Category extends Api {

    public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username', 'default' => 'PHPer', ),
            ),
        );
    }
        
    /**
    * 获取列表列表接口
    * @desc 用于获取总队的数据分类
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function getCategoryList()
    {
            $data = $_POST;
            $rs = array();
            $unit_numbers = array();  //警员的部门编号
            $this->data = array(
                    'suc_ids' => array(),
                    'fail_ids' => array(),
            );
            $domain = new DomainInformatin();
            $id = $domain->fileInfoUpload($data);
            $rs['id'] = $id;  
            return ;
    }
    
}
