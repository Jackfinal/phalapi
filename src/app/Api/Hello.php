<?php
/**
 *
 *
 * @Date   : 17/8/16 10:27
 * @author :WR.dong <wangrd@tcl.com>
 */

namespace App\Api;

use PhalApi\Api;
/**
 * 测试接口服务类
 *
 * @Date   : 17/8/16 10:27
 * @author :WR.dong <wangrd@tcl.com>
 */

class Hello extends Api
{

    /**
     * Hello World接口
     * @desc Hello World 测试接口
     * @return array
     */
    public function world()
    {
        return ['title' => 'Hello Word'];
    }
}