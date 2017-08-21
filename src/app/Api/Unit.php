<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\Unit as DomainUnit;
/**
 * 部门管理接口服务类
 *
 */

class Unit extends Api {

    public function getRules() {
        return array(
            'getUnitList' => array(
                'bmbh' 	=> array('name' => 'bmbh','require'=>true)
            ),
        );
    }
        
    /**
    * 获取部门信息接口
    * @desc 获取当前部门及下属部门信息
    * @return string bmbh 部门编号
    * @return string bmmc 部门名称
    * @return string sjbm 上级部门
    * @return string lxr 联系人
     *@return string lxdh 联系电话
    */
    public function getUnitList()
    {
        $rs = array('code' => 0, 'msg' => '', 'items' => array());
        $condition = array('bmbh'=>$this->bmbh);
        $domain = new DomainUnit();
        $list = $domain->getUnitList($condition);
        $rsdata = array();
        $i = 0;
        foreach($list as $unit){
            $rsdata[$i]['bmbh'] = $unit['Number'];
            $rsdata[$i]['bmmc'] = $unit['Name'];
            $rsdata[$i]['sjbm'] = $unit['parent_number'];
            $rsdata[$i]['lxr'] = $unit['contact'];
            $rsdata[$i]['lxdh'] = $unit['phone'];
            $i++;
        }
        $rs['items'] = $rsdata;
        $rs['code'] = 1;
        return $rs;     
    }
    
}

