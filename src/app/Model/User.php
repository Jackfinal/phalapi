<?php
/**
 *
 *
 * @Date   : 17/8/16 11:10
 * @author :WR.dong <wangrd@tcl.com>
 */

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

/**
 * Class User
 * @package App\Model
 */
class User extends  NotORM
{
    public function test()
    {
        $user = $this->getORM();

        return $user;
    }
}