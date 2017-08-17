<?php
namespace App\Api;

use PhalApi\Api;

/**
 * 部门管理接口服务类
 *
 */

class Unit extends Api {

    public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username', 'default' => 'PHPer', ),
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
    public function getUnit()
    {
            $dept_num = $_POST ['dept_num'];
            if (strlen ( $dept_num ) <= 0) {
                    $this->msg = "Failed to get parameters";
                    return;
            }
            $dept_numbers = Toolkit::getAllChildDeptId ( $dept_num );
            array_push($dept_numbers,$dept_num);
            $sql = "select * from sdv_unit where Number in ('" . implode ( "','", $dept_numbers ) . "')";
            $this->data = $this->mysql->fetchArray ( $sql );
            $this->code = 1;
    }
    
}

