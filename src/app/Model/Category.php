<?php
namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;


class Category extends NotORM {

    protected function getTableName($id) {
        return 'category';
    }
    public function getListItems($condition) {
        return $this->getORM()
            ->select('*')
            ->order('id DESC')
            ->fetchAll();
    }
}
