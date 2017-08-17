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
                'unit_number' 	=> array('name' => 'unit_number', 'require' => true),
                'page' 	=> array('name' => 'page', 'default' => '1' ),
                'perpage' 	=> array('name' => 'perpage', 'default' => '20' ),
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
    * @return string jybh 警员标号
    * @return string jyxm 警员姓名
    * @return string jyxb 警员性别
    * @return int jyzt 警员状态
     *@return string bmbh 部门编号 
    */
    public function getUserList()
    {
        $rs = array('code' => 0, 'msg' => '', 'items' => array(),'total'=>'');
        $condition = array('unit_number'=>$this->unit_number); 
        $page = $this->page;
        $perpage = $this->perpage;
        $domain = new DomainUser();
        $info = $domain->getList($condition,$page,$perpage);
        /*foreach($info as $user){
            $rs[]['jybh'] = $user['police_num'];
            $rs[]['jyxm'] = $user['name'];
            $rs[]['jyxb'] = $user['sex'];
            $rs[]['dhhm'] = $user['mobile_num'];
            $rs[]['jyzt'] = $user['status'];
            $rs[]['bmbh'] = $user['Number'];
        }*/
        $rs['items'] = $info['items'];
        $rs['total'] = $info['total'];
        $rs['code'] = 1;
        return $rs;
    }
    
}
