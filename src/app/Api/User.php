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
                'bmbh' 	=> array('name' => 'bmbh', 'require' => true,'desc'=> '部门编号')
            ),
            'userInfoUpload' => array(
            'yhbh' => array('name' => 'yhbh','require'=>true,'desc'=> '用户编号'),
            'yhxm' => array('name' => 'yhxm','require'=>true,'desc'=> '用户姓名'),
            'yhxb' => array('name' => 'yhxb','require'=>true,'desc'=> '用户性别'),
            'dhhm' => array('name' => 'dhhm','require'=>true,'desc'=> '电话号码'),
            'bmbh' => array('name' => 'bmbh','require'=>true,'desc'=> '部门编号'),
            ),
        );
    }
        
    /**
    * 用户上传接口
    * @desc 用于支队上传用户数据
    * @return int code 状态码 1为成功 0为失败
    * @return string msg  错误信息
    */
    public function userInfoUpload()
    {
            $data = $_POST;
            $rs = array('code' => 0, 'msg' => '');
            $user = array();  
            $this->data = array(
                    'suc_ids' => array(),
                    'fail_ids' => array(),
            );
            $user['police_num'] = $data['yhbh'];
            $user['name'] = $data['yhxm'];
            $user['sex'] = $data['yhxb'];
            $user['mobile_num'] = $data['dhhm'];
            $user['Number'] = $data['bmbh'];
           // return $user;
            $domain = new DomainUser();
            $id = $domain->userInfoUpload($user);
            $rs['code'] = 1;  
            return $rs;
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
