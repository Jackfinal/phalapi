<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\User as DomainUser;
/**
 * 用户管理接口服务类
 *
 */

class User extends Api {

    public function getRules() {
        return array(
            'getUserList' => array(
                'bmbh' 	=> array('name' => 'bmbh', 'default' => '0', ),
            ),
        );
    }
        
    /**
    * 用户上传接口
    * @desc 用于支队上传用户数据
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function userInfoUpload()
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
    
    /**
    * 获取当前部门和下属部门用户
    * @desc 获取当前部门和下属部门用户
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function getUserList()
    {
        $rs = array();
        $condition = array('bmbh'=>$this->bmbh); 
        $domain = new DomainUser();
        $info = $domain->getList($condition);
        $rs = $info;
        return $rs;
    }
    
}
