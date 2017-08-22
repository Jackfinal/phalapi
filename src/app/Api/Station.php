<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\Station as DomainStation;
/**
 * 站点管理接口服务类
 *
 */

class Station extends Api {

    public function getRules() {
        return array(
            'stationInfoUpload' => array(
            'ip' => array('name' => 'ip','require'=>true,'desc'=> 'IP地址'),
            'name' => array('name' => 'name','require'=>true,'desc'=> '站点名称'),
            'storage_size' => array('name' => 'storage_size','require'=>true,'desc'=> '存储空间大小'),
            'storage_rest' => array('name' => 'storage_rest','require'=>true,'desc'=> '剩余空间'),
            'memory_rate' => array('name' => 'memory_rate','require'=>true,'desc'=> '内存使用率'),
            'cpu_rate' => array('name' => 'cpu_rate','require'=>true,'desc'=> 'cpu占用率'),
            'mac_addr' =>  array('name' => 'mac_addr','require'=>true,'desc'=> 'MAC地址'),
            'server_version' =>  array('name' => 'server_version','require'=>false,'desc'=> '软件版本号'),
            'address' =>  array('name' => 'address','require'=>false,'desc'=> '地址'),
            'manager' =>  array('name' => 'manager','require'=>false,'desc'=> '管理员名称'),
            'phone' =>  array('name' => 'phone','require'=>false,'desc'=> '管理员电话'),
            'station_number' =>  array('name' => 'station_number','require'=>true,'desc'=> '站点编号'),
            'unit_number' =>  array('name' => 'unit_number','require'=>true,'desc'=> '部门编号'),
            'type' =>  array('name' => 'type','require'=>true,'desc'=> '站点类型'),
            'ftpIp' => array('name' => 'ftpIp','require'=>true,'desc'=> 'FTP IP地址'),
            'ftp_user' => array('name' => 'ftp_user','require'=>false,'desc'=> 'FTP用户名'),
            'ftp_pass' => array('name' => 'ftp_pass','require'=>false,'desc'=> 'FTP密码'),
            'merchant' => array('name' => 'merchant','require'=>true,'desc'=> '厂家'),
            ),
        );
    }
        
    /**
    * 站点信息上传接口
    * @desc 站点信息上传接口
    * @return int code 状态码 1成功 0 失败
    * @return string msg 错误信息
    */
    public function stationInfoUpload()
    {
            $data = $_POST;
            $rs = array('code' => 0, 'msg' => '');
            $domain = new DomainStation();
            $id = $domain->stationInfoUpload($data);
            $rs['code'] = 1;
            return $rs;
    }
   
}
