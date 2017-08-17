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
                'unit_number' 	=> array('name' => 'unit_number','require'=>true),
                'page' 	=> array('name' => 'page', 'default' => '1', ),
                'perpage' 	=> array('name' => 'perpage', 'default' => '20', ),
            ),
        );
    }
        
    /**
    * 获取部门信息接口
    * @desc 获取当前部门及下属部门信息
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function getUnitList()
    {
        $rs = array('code' => 0, 'msg' => '', 'items' => array());
        $condition = array('unit_number'=>$this->unit_number);
        $page = $this->page;
        $perpage = $this->perpage;
        $domain = new DomainUnit();
        $list = $domain->getUnitList($condition,$page,$perpage);
        $rs['items'] = $list;
        $rs['code'] = 1;
        return $rs;     
    }
    
}

