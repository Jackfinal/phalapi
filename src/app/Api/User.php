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
                'bmbh' 	=> array('name' => 'bmbh', 'require' => true)
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
    * @return string yhbh 用户编号
     * @return string yhbh 用户密码
    * @return string yhxm 用户姓名
    * @return string yhxb 用户性别
    * @return string dhhm 电话号码
     *@return string yhjs 角色编号 
     *@return string bmbh 部门编号 
    */
    public function getUserList()
    {
        $rs = array('code' => 0, 'msg' => '', 'items' => array());
        $condition = array('bmbh'=>$this->bmbh); 
        $domain = new DomainUser();
        $list = $domain->getList($condition);
        $rsdata = array();
        $i = 0;
        foreach($list as $user){
            $rsdata[$i]['yhbh'] = $user['police_num'];
            $rsdata[$i]['yhmm'] = $user['password'];
            $rsdata[$i]['yhxm'] = $user['name'];
            $rsdata[$i]['yhxb'] = $user['sex'];
            $rsdata[$i]['dhhm'] = $user['mobile_num'];
            $rsdata[$i]['yhjs'] = $user['role_id'];
            $rsdata[$i]['bmbh'] = $user['Number'];
            $rsdata[$i]['yhzt'] = $user['status'];
            $i++;
        }
        $rs['items'] = $rsdata;
        $rs['code'] = 1;
        return $rs;
    }
    
}
