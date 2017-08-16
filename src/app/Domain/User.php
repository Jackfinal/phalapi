<?php
/**
 *
 *
 * @Date   : 17/8/16 11:40
 * @author :WR.dong <wangrd@tcl.com>
 */

namespace App\Domain;

use App\Model\User as ModelUser;

class User
{
    /**
     * @param $id
     * @return mixed
     */
    public function get($id) {
        $model = new ModelUser();
        return $model->get($id);
    }
}