<?php
/**
 *
 *
 * @Date   : 17/8/16 10:46
 * @author :WR.dong <wangrd@tcl.com>
 */

namespace App\Api;

use PhalApi\Api;
use App\Domain\User as DomaUser;
/**
 * 用户接口
 *
 * @Date   : 17/8/16 10:46
 * @author :WR.dong <wangrd@tcl.com>
 */
class User extends Api
{


    public function getRules() {
        return array(
            'login' => array(
                'username' => array('name' => 'username', 'desc' => '用户名'),
                'password' => array('name' => 'password'),
            ),
        );
    }

    /**
     * 登录
     * @desc 登录
     * @return array
     */
    public function login() {
        return array('username' => $this->username, 'password' => $this->password);
    }

    /**
     * 登录
     * @desc 登录
     * @return array
     */
    public function logout() {
        return array();
    }

    public function info()
    {
        $domain = new DomaUser();
        $rs = $domain->get(1);

        $this->pdebug($rs);
        echo 'ddd';
        exit;
    }

}