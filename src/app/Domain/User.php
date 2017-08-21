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
    /**
     * 数据管理 列表 查询条件
     * @param array $condition
     * @return array
     */
    /*public static function getInformationWhere($condition = array())
    {
        $_where = $condition['where'];
        $where = "1=1";
        $whereArr = array();
        if (isset($_where['police_num']) && !empty($_where['police_num'])) {
            $where .= " and police_num=:police_num";
            $whereArr[':police_num'] = $_where['police_num'];
        }
        if (isset($_where['equipment_num']) && !empty($_where['equipment_num'])) {
            $where .= " and equipment_num=:equipment_num";
            $whereArr[':equipment_num'] = $_where['equipment_num'];
        }
        if (isset($_where['type']) && !empty($_where['type'])) {
            $where .= " and type=:type";
            $whereArr[':type'] = $_where['type'];
        }else{
            $where .= " and type<>:type";
            $whereArr[':type'] = "log";
        }
        if (isset($_where['level']) && !empty($_where['level'])) {
            $where .= " and level=:level";
            $whereArr[':level'] = $_where['level'];
        }
        if (isset($_where['del_status']) && !empty($_where['del_status'])) {
            if ($_where['del_status'] == '1') {
                $where .=" and (del_status in (2,3) or (del_status=1 and existed_file = 0))";
            } else if ($_where['del_status'] == '0') {
                $where .=" and (del_status = 0 or (del_status = 1 and existed_file = 1))";
            } else {
                $where .=" and del_status=:del_status";
                $whereArr[':del_status'] = $_where['del_status'];
            }
        } else {
            $where .=" and (del_status = 0 or (del_status = 1 and existed_file = 1))";
        }
        if (isset($_where['category_id']) && !empty($_where['category_id'])) {
            $where .= " and category_id=:category_id";
            $whereArr[':category_id'] = $_where['category_id'];
        }
        if (isset($_where['bt']) && !empty($_where['bt'])) {
            $where .= " and record_date >=:bt";
            $whereArr[':bt'] = $_where['bt'];
        }
        if (isset($_where['et']) && !empty($_where['et'])) {
            $where .= " and record_date <:et";
            $whereArr[':et'] = $_where['et'];
        }
        if (isset($_where['upbt']) && !empty($_where['upbt'])) {
            $where .= " and upload_date >=:upbt";
            $whereArr[':upbt'] = $_where['upbt'];
        }
        if (isset($_where['upet']) && !empty($_where['upet'])) {
            $where .= " and upload_date <:upet";
            $whereArr[':upet'] = $_where['upet'];
        }    
        $userWhere = array();
        if(isset($_where['own_dept'])&& $_where['own_dept'] != null){
            $unit_numbers = array();
            if($_where['own_dept'] != -99) {
                $unit_numbers = $_where['own_dept'];
            }
            if (isset($_where['unit_number']) && !empty($_where['unit_number'])) {
                if(isset($_where['isContainSon']) && $_where['isContainSon']==1){
                    $unit_numbers2 = Toolkit::getAllChildDeptId($_where['unit_number']);
                    array_push($unit_numbers2, $_where['unit_number']);
                } else {
                    $unit_numbers2[] = $_where['unit_number'];
                }
                if (!empty($unit_numbers)) {
                    $unit_numbers = array_intersect($unit_numbers, $unit_numbers2);
                } else {
                    $unit_numbers = $unit_numbers2;
                }
            }
            if (count($unit_numbers) > 0) {
                //$userWhere['NumberArr'] = $unit_numbers;
                $where .= " and unit_number in ('" . implode("','", $unit_numbers) . "')";
            }
        } else {
            $where .= " and police_num=:my_police_num";
            $whereArr[':my_police_num'] = $_SESSION['user']['police_num'];
        }
        if (isset($_where['police_classification_id']) && !empty($_where['police_classification_id'])) {
            $userWhere['police_classification_id'] = $_where['police_classification_id'];
        }
        if (isset($_where['police_name']) && !empty($_where['police_name'])) {
            $userWhere['name'] = $_where['police_name'];
        }
        if (count($userWhere) > 0) {
            $police_numArr = User::getPolice_numArr($userWhere);
            $where .= " and police_num in ('" . implode("','", $police_numArr) . "')";
        }
        $inforDescWhere = '';
        if ($_where['description'] !== null && $_where['description'] !== '') {
            $description = addslashes($_where['description']);
            $inforDescWhere .= " and description like binary('%{$description}%')";
        }
        if ($_where['remark'] !== null && $_where['remark'] !== '') {
            $remark = addslashes($_where['remark']);
            $inforDescWhere .= " and remark like binary('%{$remark}%')";
        }
        if ($inforDescWhere != '') {
            //连表效率 太差了 用子查询
            $where .= " and archive_num in (select archive_num from sdv_information_desc where 1=1 {$inforDescWhere})";
        }
        return array($where, $whereArr);
    }
    
    public function fileInfoUpload()
    {
        $ftp_users = Stations::getStationFtpAll();
        $apache_ftp_path = Config::getConfigByType('apache_ftp_path');
        foreach ($data as $key => $val) {
                if (!is_array($val) || !isset($val['id'])) {
                        continue;
                }
                $police_num = $val['police_num'];
                if (isset($unit_numbers[$police_num]) && !empty($unit_numbers[$police_num])) {
                        $unit_number = $unit_numbers[$police_num];
                } else {
                        $inf = Yii::app()->db->createCommand("select Number from sdv_user where police_num='{$police_num}'")->queryRow();
                        $unit_number = empty($inf['Number']) ? '' :  $inf['Number'];
                }
                $ftp_user = empty($ftp_users[$val['station_id']]) ? "" : trim($ftp_users[$val['station_id']]);
                $path = empty($apache_ftp_path[$ftp_user]) ? '' : $ftp_user;
                $insertArr = array(
                        'police_num'    => empty($val['police_num']) ? '' : trim($val['police_num']),
                        'equipment_num' => empty($val['equipment_num']) ? '' : trim($val['equipment_num']),
                        'file_name'     => empty($val['file_name']) ? '' : trim($val['file_name']),
                        'size'          => intval($val['size']),
                        'type'          => trim($val['type']),
                        'level'         => intval($val['level']),
                        'record_date'   => intval($val['record_date']),
                        'upload_date'   => intval($val['upload_date']),
                        'station_id'    => empty($val['station_id']) ? 0 : $val['station_id'],
                        'unit_number'   => $unit_number,
                        'path'          => $path,//apache别名，ftp用户名
                        'totalTime'     => intval($val['totalTime']),
                        'archive_num'   => trim($val['archive_num']),
                        'existed_file'  => intval($val['existed_file']),
                        'trans_filename' => '',
                        'trans_status' => 0
                );
                //判断该索引是否已经存在,不存在则写入
                $info = Information::getDataByArchiveNum($insertArr['archive_num'],array('id'));
                if ($info) {
                        //已存的就是上报成功的了
                        $this->data['suc_ids'][] = $val['id'];
                        continue;
                }
                try {
                        $re = Yii::app()->db->createCommand()->insert("{{information}}", $insertArr);
                } catch(Exception $e) {
                        $re = 0;
                        //返回错误信息
                        $this->msg[] = $insertArr['archive_num'] . " : " . $e->getMessage();
                }
                //返回处理数据---发现tcl_sdv.sdv_information 表中 archive_num 没有上索引，所以这里返回数据用了 id 主键
                if ($re) {
                        $this->data['suc_ids'][] = $val['id'];
                } else {
                        $this->data['fail_ids'][] = $val['id'];
                }
        }

    }*/
    
}
