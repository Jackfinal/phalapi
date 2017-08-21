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
                'stationInfo' 	=> array('name' => 'stationInfo','require'=>true),
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
            $stationInfo = $this->stationInfo;
            $data = json_decode($stationInfo);
            $rs = array();
            $domain = new DomainStation();
           $id = $domain->insert($data);
            $rs = $id;  
            return $rs;
    }
   
}
