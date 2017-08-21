<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\Category as DomainCategory;
/**
 * 类别管理接口服务类
 *
 */

class Category extends Api {

    public function getRules() {
        return array(
            
        );
    }
        
    /**
    * 获取列表列表接口
    * @desc 用于获取总队的数据分类
    * @return int flid  分类ID
    * @return string flmc  分类名称
    * @return int flzt  删除状态 
    * @return int    ccsj  存储时间
     * @return int sjfl 上级分类ID
    */
    public function getCategoryList()
    {
            $rs = array('code' => 0, 'msg' => '', 'items' => array());
            $condition = array(); 
            $domain = new DomainCategory();
            $list = $domain->getList($condition);
            $rsdata = array();
            $i = 0;
            foreach($list as $item){
                $rsdata[$i]['flid'] = $item['id'];
                $rsdata[$i]['flmc'] = $item['cate_name'];
                $rsdata[$i]['flzt'] = $item['del_status'];
                $rsdata[$i]['ccsj'] = $item['storage_time'];
                $rsdata[$i]['sjfl'] = $item['pid'];
                $i++;
            }
            $rs['items'] = $rsdata;
            $rs['code'] = 1;
            return $rs;
    }
    
}
