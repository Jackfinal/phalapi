<?php
namespace App\Domain;

use App\Model\Cagegory as ModelCagegory;

class Category {

    public function insert($newData) {
        $newData['post_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

        $model = new ModelInformation();
        return $model->insert($newData);
    }

    public function update($id, $newData) {
        $model = new ModelInformation();
        return $model->update($id, $newData);
    }

    public function get($id) {
        $model = new ModelInformation();
        return $model->get($id);
    }

    public function delete($id) {
        $model = new ModelInformation();
        return $model->delete($id);
    }

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
    public function getUnit() {
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
