<?php
namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;
use App\Common\Tools as CommonTools;


class User extends NotORM {

    protected function getTableName($id) {
        return 'user';
    }
     
    public function getListItems($condition) {
        return $this->getORM()
            ->select('*')
            ->where('Number', $condition['bmbh'])
            ->order('id DESC')
            ->fetchAll();
    }

    public function addUser($user)
    {
        $userCount = $this->getUserByNum($user['police_num']);
        if($userCount){
            $rs = $this->getORM()->where('police_num',$station['police_num'])->update($user);
        }else{
            $user['role_id'] = 4;
            $user['password'] = $user['police_num'];
            $rs = $this->getORM()->insert($user);
        }
        return $rs;
    }
    
    public function getUserByNum($police_num) {
        return $this->getORM()->select('*')->where('police_num', $police_num)->fetchOne();
    }
}
