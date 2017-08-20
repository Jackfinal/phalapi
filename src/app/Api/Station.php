<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\Station as DomainStation;
/**
 * 站点管理接口服务类
 *
 */

class Station extends Api {

    public function getRules() {
        return array(
            'stationInfoUpload' => array(
                'stationInfo' 	=> array('name' => 'stationInfo','require'=>true),
            ),
        );
    }
        
    /**
    * 站点信息上传接口
    * @desc 站点信息上传接口
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function stationInfoUpload()
    {
            $stationInfo = $this->stationInfo;
            $data = json_decode($stationInfo);
            $rs = array();
            $domain = new DomainStation();
           $id = $domain->insert($data);
            $rs = $id;  
            return $rs;
    }
    
    /**
     * 批量上传索引接口
     * @desc 批量上传索引,数据批量入库;通过POST_ROWS 方式 POST 索引列表进,每一个列表都是为包含下列参数的map数组
     * @return array suc_ids     成功入库的ids 包括(existed_ids)
     * @return array existed_ids 通过wjbh检查 已经存在的ids 不进行入库
     * @return array fail_ids    入库失败的 ids
     * @throws BadRequestException
     */
    public function puts()
    {
        $data = $_POST;
        $domain = new Domain();
        $_data = array();
        $rules = $this->putsrules();

        foreach ($data as $key => $val) {
            if (!is_array($val)) {
                throw new BadRequestException('POST 数据必须为 POST_ROWS 格式', 1);
            }
            foreach ($rules as $k => $rule) {
                $rs = Parser::format($rule['name'], $rule, $val);
                if ($rs === NULL && (isset($rule['require']) && $rule['require'])) {
                    throw new BadRequestException("第" . ($key + 1) . "条数据," . \PhalApi\T('{name} require, but miss', array('name' => $rule['name'])));
                }
                $_data[$key][$k] = $rs;
            }
        }
        $types = array('log', 'video', 'audio', 'photo');
        $levels = array(1, 3);
        $data = array();
        foreach ($_data as $key => $val) {
            $data[$key] = array(
                'id' => $val['id'],
                'archive_num'   => $val['wjbh'],
                'police_num'    => $val['yhbh'],
                'equipment_num' => $val['sbbh'],
                'file_name'     => $val['wjmc'],
                'size'          => $val['wjdx'],
                'type'          => $types[$val['mtlx']],
                'level'         => $levels[$val['zybj']],
                'record_date'   => $val['pssj'],
                'upload_date'   => $val['drsj'],
                'station_id'    => $val['gzz_xh'],
                'unit_number'   => $val['bmbh'],
                'totalTime'     => $val['wjsc'],
                'del_status' => $val['wjzt'],
                'play_path' => $val['bflj'],
                'download_path' => $val['xzlj'],
            );
        }
        return $domain->puts($data);

    }
   
}
