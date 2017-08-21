<?php
namespace App\Domain;

use App\Model\Unit as ModelUnit;

class Unit {

    public function getList($state, $page, $perpage) {
        $rs = array('items' => array(), 'total' => 0);

        $model = new ModelInformation();
        $items = $model->getListItems($state, $page, $perpage);
        $total = $model->getListTotal($state);

        $rs['items'] = $items;
        $rs['total'] = $total;

        return $rs;
    }
    
    /**
     * * 获取部门信息接口
     * @desc  根据部门编号获取当前部门和下属部门列表
    */
    public function getUnitList($condition) {
        $model = new ModelUnit();
        $dept_numbers = $model->getAllChildDeptId($condition['bmbh']);
        return $dept_numbers;
    }
    
    
}
