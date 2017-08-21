<?php
namespace App\Domain;

use App\Model\Category as ModelCategory;

class Category {


    public function getList($condition) {
        $rs = array('items' => array());
        $model = new ModelCategory();
        $rs = $model->getListItems($condition);
        return $rs;
    }
    
}
