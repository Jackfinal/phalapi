<?php
namespace App\Domain;

use App\Model\User as ModelUser;
use App\Model\Unit as ModelUnit;

class User {

    /*public function insert($newData) {
        $newData['post_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

        $model = new ModelInformation();
        return $model->insert($newData);
    }

    public function update($id, $newData) {
        $model = new ModelInformation();
        return $model->update($id, $newData);
    }

    public function get($id) {
        $model = new ModelUser();
        return $model->get($id);
    }

    public function delete($id) {
        $model = new ModelInformation();
        return $model->delete($id);
    }*/

    public function getList($condition) {
        $rs = array('items' => array());
        $model = new ModelUser();
        $modelUnit = new ModelUnit();
        $unitList = $modelUnit->getAllChildDeptId($condition['bmbh']);
        foreach ($unitList as $item) {
            $unit[] = $item['Number'];
        }
        $condition['bmbh'] = $unit;
        $rs = $model->getListItems($condition);
        return $rs;
    }
    
    public function userInfoUpload($user) {

        $model = new ModelUser();
        return $model->addUser($user);
    }
    
}
