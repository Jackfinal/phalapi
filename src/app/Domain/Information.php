<?php
namespace App\Domain;

use App\Model\Information as ModelInformation;
use PhalApi\Exception;

class Information {


    public function insert($newData) {
        $newData['post_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

        $model = new ModelInformation();
        return $model->insert($newData);
    }

    public function update($id, $newData) {
        $model = new ModelInformation();
        return $model->update($id, $newData);
    }

    public function get($id) {
        $model = new ModelInformation();
        return $model->get($id);
    }

    public function delete($id) {
        $model = new ModelInformation();
        return $model->delete($id);
    }

    public function getList($state, $page, $perpage) {
        $rs = array('items' => array(), 'total' => 0);

        $model = new ModelInformation();
        $items = $model->getListItems($state, $page, $perpage);
        $total = $model->getListTotal($state);

        $rs['items'] = $items;
        $rs['total'] = $total;

        return $rs;
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

    }

    /**
     * 数据批量入库
     * @param array $data
     * @return array
     */
    public function puts(array $data)
    {
        $model = new ModelInformation();
        $rs = array(
            'suc_ids' => array(),
            'existed_ids' => array(),
            'fail_ids' => array(),
        );
        foreach ($data as $key => $val) {
            $id = $val['id'];
            unset($val['id']);
            if (empty($val['archive_num'])) {
                $val['archive_num'] = $val['police_num'] . '_' . md5("{$id}_{$val['police_num']}_{$val['file_name']}_{$val['record_date']}_{$val['size']}");
            }
            $data = $model->getByArchiveNum($val['archive_num']);
            if ($data) {
                $rs['suc_ids'][] = $id;
                $rs['existed_ids'][] = $id;
                continue;
            }
            try {
                $model->insert($val);
                $rs['suc_ids'][] = $id;
            } catch (Exception $e) {
                $rs['fail_ids'][] = $id;
            }
        }
        return $rs;

    }
    
}
