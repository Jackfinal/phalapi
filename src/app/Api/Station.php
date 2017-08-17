<?php
namespace App\Api;

use PhalApi\Api;

/**
 * 站点管理接口服务类
 *
 */

class Station extends Api {

    public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username', 'default' => 'PHPer', ),
            ),
        );
    }
        
    /**
    * 站点信息上传接口
    * @desc 站点信息上传接口
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function stationInfoUpload()
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
