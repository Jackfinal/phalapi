<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(__FILE__) . "/PhalApiClient.php");

class ClientCommand extends CConsoleCommand {

    private $FFMPEG_FILE;
    private $dataPath;
    private $finish;
    private $loopInterval;
    private $workStation;
    private $maxProcessCount; //最多处理数量
    private $key;
    private $config;
    private $upload;
    private $ftpPort;
    private $maxInfomationCount = 100; //单次上传最大索引数
    private $maxLogCount = 1000; //单次上传最大日志数
    private $cloudIp = '';

    public function __construct($name, $runner) {
        parent::__construct($name, $runner);
        //配置初始化
        Config::configInit();
        switch (strtolower(substr(PHP_OS, 0, 3))) {
            case 'win':
                $this->FFMPEG_FILE = dirname(dirname(dirname(__FILE__))) . '/ffmpeg/ffmpeg.exe';
                break;
            case 'lin':
                $this->FFMPEG_FILE = '/usr/local/bin/ffmpeg';
                break;
            default:
                die('not support system   ' . PHP_OS);
        }

        set_time_limit(0);

        //读取配置文件
        $config = include(ROOT_PATH . "/protected/config/main.php");
        $this->config = $config;
        $this->dataPath = $config['params']['dataPath'];
        $this->loopInterval = $config['params']['onlineInterval'];
        $this->finish = false;
        $this->maxProcessCount = 1000;
        //读取工作站信息
        $this->workStation = Workstation::model()->find();
        $this->cloudIp = $this->workStation && $this->workStation->cloudIp > 0 ? long2ip($this->workStation->cloudIp) : '';
        $this->key = $this->workStation->access_key;
        $this->upload = Upload::model()->find();
        $this->ftpPort = Yii::app()->params['ftpPort'] !== NULL ? Yii::app()->params['ftpPort'] : '21';
    }

    /**
     * 每分钟执行一次的方法
     *
     */
    public function actionOneMinute() {
        //心跳
        $this->heartbeat();
        //上报索引--下发公告
        $this->lightRun();
    }

    /**
     * 哥伦比亚备份音视频
     * 每分钟执行一次的方法
     *
     */
    public function actionOneMinute1() {
        $this->syncTime();
    }

    /**
     * 每五分钟执行一次的方法
     *
     */
    public function actionFiveMinute() {
        //上传数据
        $this->weightRun();
    }

    /**
     * 每半小时执行一次的方法
     *
     */
    public function actionHalfAnHour() {

    }

    /**
     * 每小时执行一次的方法
     *
     */
    public function actionOneHour() {
        //更新工作站相关的信息
        $this->weightRun2();
    }


    /**
     * 每两小时执行一次的方法
     *
     */
    public function actionTwoHour() {

    }

    /**
     * 心跳
     *
     */
    public function heartbeat() {
        try {
            $this->actionPutWsInfo();
        } catch (Exception $e) {
            echo "\n heartbeat error:\n" . $e->getMessage();
        }
    }

    /**
     * 上报工作
     *
     */
    public function lightRun() {
        //设置了二级服务器才能上报
        if ($this->workStation->cloudIp > 0) {
            //上报索引
            $this->actionPutinformations(0);
            //下发公告
            $this->actionNotice();
        }
    }

    /**
     * 上传数据
     */
    public function weightRun() {
        ini_set("memory_limit", "600M");
        // 默认早上10点10分执行上传任务
        $uploadtask_time = $this->workStation->uploadtask_time;
        $uploadtask_end = $this->workStation->uploadtask_end;
        if (strlen($uploadtask_time) <= 0) {
            $uploadtask_time = "20:30:00";
        }
        $time = date('H:i');
        if ($this->workStation->cloudIp > 0) {
            // 寻找所有未上传的数据,最多1000条
            try {
                if ($time > substr($uploadtask_time, 0, 5) || $time < substr($uploadtask_end, 0, 5)) {
                    if ($this->workStation->net_way == 1) {
                        $this->ftpUploadFileOne();
                    } elseif ($this->workStation->net_way == 2) {
                        $this->ftpUploadFileTwo();
                    } else {
                        
                    }

                    //3.上报日志信息
                    //$this->actionPutWsLog();
                    $this->actionPutWsLogs();
                }
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
    
    public function getUserList()
    {
        $cloudIp = long2ip($this->workStation->cloudIp);
        $unit_number = $this->workStation->unit_number;
        $client = PhalApiClient::create()
            ->withHost('http://' . $cloudIp . '/api/public/index.php');
        $rs = $client->reset()
            ->withService('User.getUserList')
            ->withParams('unit_number', $unit_number)
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();

        if ($rs->getRet() == 200 && is_array($rsdata['items']) && count($rsdata['items']) > 0) {
            foreach ($rsdata['items'] as $data) {
                $users = array(
                'id' => $data['id'],
                'police_num' => $data['police_num'],
                'password' => $data['password'],
                'name' => $data['name'],
                'sex' => $data['sex'],
                'mobile_num' => $data['mobile_num'],
                'role_id' => $data['role_id'],
                'desc' => $data['desc'],
                'created_date' => $data['created_date'],
                'created_by' => $data['created_by'],
                'status' => $data['status'],
                'Number' => $data['Number']
                );
                $userCount = User::getUserByPoliceNum($data['police_num']);
                if(empty($userCount)){
                    $user_result = Yii::app()->db->createCommand()->insert('{{user}}', $users);
                }else{
                    $user_result = Yii::app()->db->createCommand()->update('{{user}}', $users,'police_num=:police_num', array(':police_num' => $data['police_num']));
                }
                
            }
        }
    }
    /**
     * 更新工作站相关的信息
     *
     */
    public function weightRun2() {
        set_time_limit(0);
        ini_set("memory_limit", "100M");
        try {
            // 更新用户表
            if ($this->workStation->cloudIp > 0) {
                    $this->getUserList();
                    $this->getUnitList();
                    //更新权限部门信息
                    /*$this->actionUpdatePermission();
                    $this->actionGetMatcheByNumber();
                    $this->actionPutinformations(2);*/
            
               
            } else {
               
            }
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
            

            

    //删除文件上报
    public function notifyDeleteFile($archive_num) {
        echo $archive_num;
        $result = $this->exec_curl(Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp), 'NotifyDeleteFile', $this->key), array('archive_num' => $archive_num));
        return $result;
    }

            

            

            

    /**
     * 上传文件方式二(从平台获取上传文件信息和ftp信息,再上传数据文件)
     * @return bool
     */
    public function ftpUploadFileOne() {
        $data = $this->actionGetimportantfile();
        if (empty($data)) {
            //没有要上传的文件
            return true;
        }
        $ftpinfo = $this->getFTPInfo();
        if (empty($ftpinfo)) {
            //没有可用的存储服务器
            return true;
        }
        $testRs = Toolkit::testConntectFtp($ftpinfo['ftpip'], $ftpinfo['ftpUser'], $ftpinfo['ftpPwd'], $ftpinfo['ftpPort'], true);
        print_r($ftpinfo);
        if ($testRs['status'] != 1) {
            //ftp error
            return true;
        }
        $ftp = $testRs['ftp'];
        foreach ($data as $key => $val) {
            //先创建文件夹
            $file = Toolkit::getInformationFileRealPath($val);
            echo $file;
            $val['station_number'] = $val['station_id'];
            $mkdir = dirname(Toolkit::getInformationFilePath($val));
            $upload_status = 0;
            if (is_file($file)) {
                @ftp_mkdir($ftp, $mkdir);
                $fp = @fopen($file, 'rb');
                $cloudFileName = $mkdir . '/' . $val['file_name'];
                $length = @ftp_size($ftp, $cloudFileName);
                $upload_status = @ftp_fput($ftp, $cloudFileName, $fp, FTP_BINARY, $length);
                @fclose($fp);
            } else {
                $this->notifyDeleteFile($val['archive_num']);
                Yii::app()->db->createCommand("update sdv_information set del_status=1 where archive_num='{$val['archive_num']}")->execute();
                continue;
            }
            if ($upload_status) {
                $result = $this->exec_curl(Toolkit::getTclApiUrl($this->cloudIp, 'updateFileStatus', $this->key), array(
                    'storageNumber' => $ftpinfo['storageNumber'],
                    'ftpAlias' => $ftpinfo['ftpAlias'],
                    'archive_num' => $val['archive_num'],
                    'police_num' => $val['police_num'],
                ));
                $result = json_decode($result, true);
                if ($result['code'] == 1) {
                    if ($this->workStation->uploaded_del == 1) {
                        @unlink($file);
                        Yii::app()->db->createCommand("update sdv_information set del_status=1,existed_file=2 where archive_num='{$val['archive_num']}'")->execute();
                    } else {
                        Yii::app()->db->createCommand("update sdv_information set existed_file=2 where archive_num='{$val['archive_num']}'")->execute();
                    }
                }
            }
        }
        ftp_close($ftp);
    }

    /**
     * 上传文件方式三(上传本地所有,再上传数据文件)
     * @return bool
     */
    public function ftpUploadFileTwo() {
        $data = Yii::app()->db->createCommand()->select('*')->from('sdv_information')->where('status=1 and existed_file=1 and del_status!=1')->limit(10)->queryAll();
        if (empty($data)) {
            //没有要上传的文件
            return true;
        }
        $ftpinfo = $this->getFTPInfo();
        if (empty($ftpinfo)) {
            //没有可用的存储服务器
            return true;
        }
        $testRs = Toolkit::testConntectFtp($ftpinfo['ftpip'], $ftpinfo['ftpUser'], $ftpinfo['ftpPwd'], $ftpinfo['ftpPort'], true);
        print_r($ftpinfo);
        if ($testRs['status'] != 1) {
            //ftp error
            return true;
        }
        $ftp = $testRs['ftp'];
        foreach ($data as $key => $val) {
            //先创建文件夹
            $file = Toolkit::getInformationFileRealPath($val);
            $val['station_number'] = $this->workStation->station_number;
            $mkdir = dirname(Toolkit::getInformationFilePath($val));
            $upload_status = 0;
            if (is_file($file)) {
                @ftp_mkdir($ftp, $mkdir);
                $fp = @fopen($file, 'rb');
                $cloudFileName = $mkdir . '/' . $val['file_name'];
                $length = @ftp_size($ftp, $cloudFileName);
                $upload_status = @ftp_fput($ftp, $cloudFileName, $fp, FTP_BINARY, $length);
            } else {
                $this->notifyDeleteFile($val['archive_num']);
                Yii::app()->db->createCommand("update sdv_information set del_status=1 where archive_num='{$val['archive_num']}'")->execute();
                continue;
            }
            if ($upload_status) {
                $result = $this->exec_curl(Toolkit::getTclApiUrl($this->cloudIp, 'updateFileStatus', $this->key), array(
                    'storageNumber' => $ftpinfo['storageNumber'],
                    'ftpAlias' => $ftpinfo['ftpAlias'],
                    'archive_num' => $val['archive_num'],
                    'police_num' => $val['police_num'],
                ));
                $result = json_decode($result, true);
                if ($result['code'] == 1) {
                    if ($this->workStation->uploaded_del == 1) {
                        @fclose($fp);
                        @unlink($file);
                        Yii::app()->db->createCommand("update sdv_information set del_status=1,existed_file=2 where archive_num='{$val['archive_num']}'")->execute();
                    } else {
                        Yii::app()->db->createCommand("update sdv_information set existed_file=2 where archive_num='{$val['archive_num']}'")->execute();
                    }
                }
            }
        }
        ftp_close($ftp);
    }

    /**
     * 从平台获取可用的ftp信息
     * @return array
     */
    public function getFTPInfo() {
        $platform_storage_type = Yii::app()->params['platform_storage_type'];
        $ftpInfo = array();
        if ($platform_storage_type == '2') {
            $result = $this->exec_curl(Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp), 'GetFTPInfo', $this->key), array('station_number' => $this->workStation->station_number));
            $result = json_decode($result, true);
            if (isset($result['code']) && $result['code'] == 1) {
                return $result['data'];
            } else {
                return array();
            }
            $workstation_number = $this->workstation['station_number'];
            $ftpInfo = $this->post('getFTPInfo', array('Number' => $workstation_number));
        } else {
            $ftpInfo['ftpip'] = $this->upload->ip;
            $ftpInfo['ftpPort'] = 21;
            $ftpInfo['ftpUser'] = $this->upload->name;
            $ftpInfo['ftpPwd'] = $this->upload->password;
            $ftpInfo['storageNumber'] = "";
            $ftpInfo['ftpAlias'] = $this->upload->name;
        }
        if (isset($ftpInfo['ftpip'])) {
            return $ftpInfo;
        }
        return false;
    }

            

    //获取公告
    public function actionNotice() {
        $workStation = Yii::app()->db->createCommand("select * from sdv_workstation  limit 1")->queryRow();
        if ($workStation['cloudIp'] <= 0)
            return false;
        $result = $this->exec_curl(Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp), "getNotice", $this->key), array('unit_number' => $this->workStation->unit_number));
        $result = json_decode($result, true);

        $codes1 = array();
        if ($result ['code'] == 1 && is_array($result ['data']) && count($result ['data']) > 0) {
            // 删除所有的公告
            // Yii::app ()->db->createCommand ( "delete from sdv_notice" )->execute ();
            foreach ($result ['data'] as $data) {
                $permission = array(
                    'id' => $data['id'],
                    'title' => $data ['title'],
                    'content' => $data ['content'],
                    'created_date' => $data ['created_date'],
                    'unit_number' => $data ['unit_number'],
                    'created_by' => $data ['created_by'],
                    'send_date' => $data['send_date'],
                    'deadline' => $data ['deadline'],
                    'type' => $data ['type'],
                    'number_all' => $data ['number_all']
                );
                $notice = Yii::app()->db->createCommand("select id from sdv_notice where id = " . $permission['id'] . "")->queryRow();

                if (!$notice) {
                    // 执行导入
                    Yii::app()->db->createCommand()->insert('{{notice}}', $permission);
                } else {
                    // 执行更新
                    Yii::app()->db->createCommand()->update('{{notice}}', $permission, 'id=:id', array(':id' => $data['id']));
                }
                $codes1[] = $permission['id'];
            }
            $notice1 = Yii::app()->db->createCommand("select id from sdv_notice ")->queryAll();
            $codes = array();
            foreach ($notice1 as $row) {
                $codes[] = $row['id'];
            }
            $result = array_diff($codes, $codes1);
            if ($result) {
                $id = implode(",", $result);
                Yii::app()->db->createCommand("delete from sdv_notice where id in ({$id})")->execute();
            }
        }
    }
    public function getUnitList() {
        $cloudIp = long2ip($this->workStation->cloudIp);
        $unit_number = $this->workStation->unit_number;
        $client = PhalApiClient::create()
            ->withHost('http://' . $cloudIp . '/api/public/index.php');
        $rs = $client->reset()
            ->withService('Unit.getUnitList')
            ->withParams('unit_number', $unit_number)
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();
        //print_r($rsdata);die();
        if ($rs->getRet() == 200 && is_array($rsdata) && count($rsdata) > 0) {
            foreach ($rsdata['items'] as $data) {
                $unit = array(
                    'id' => $data['id'],
                    'Name' => $data['Name'].'aaa',
                    'Number' => $data['Number'],
                    'Desc' => $data['Desc'],
                    'parent_number' => $data['parent_number'],
                    'contact' => $data['contact'],
                    'phone' => $data['phone'],
                    'created_date' => $data['created_date'],
                    'child_count' => $data['child_count']
                );
                $unitCount = Unit::getUnitNameByNumber($data['Number']);
                //echo $data['Number'];print_r($unitCount);die();
                if(empty($unitCount)){
                    Yii::app()->db->createCommand()->insert('{{unit}}', $unit);
                }else{
                    Yii::app()->db->createCommand()->update('{{unit}}', $unit,'Number=:Number',array(':Number' => $data['Number']));
                }
                
            }
        }
    }

            

    private function exec_curl($url, $post = array(), $isPost = true, $timeout = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($isPost) {
;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        $result = curl_exec($ch); # 得到的返回值
        curl_close($ch);

        return $result;
    }
            

    /**
     * 批量上传索引
     * @param int $status 0:上传未上传的索引,2: 再次上传 上传失败 的索引
     * @return bool
     */
    public function actionPutinformations($status = 0) {
        if ($this->workStation->cloudIp <= 0) {
            return false;
        }
        if ($status == 0) {
            $where = "status = 0";
        } elseif ($status == 2) {
            $e_time = 30 * 86400; //7天之前上传失败的不再次上传
            $where = "status = 2 and upload_date >= {$e_time}";
        }
        //exit;
        //传输数据索引部分
        $datas = Yii::app()->db->createCommand()
            ->from("sdv_information")
            ->select("*")
            ->where($where)
            ->limit($this->maxInfomationCount)
            ->order("id desc")
            ->queryAll();
        $_data = array();
        $types = array(
            'log' => 0,
            'video' => 1,
            'audio' => 2,
            'photo' =>3
        );
        foreach ($datas as $key => $data) {
            $_data[$key] = array(
                'id' => $data['id'],
                'yhbh' => $data['police_num'],
                'sbbh' => $data['equipment_num'],
                'wjmc' => $data['file_name'],
                'wjdx' => $data['size'],
                'mtlx' => $types[$data['type']],
                'zybj' => $data['level'] == 3 ? 1 : 0,
                'pssj' => date('Y-m-d H:i:s', $data['record_date']),
                'drsj' => date('Y-m-d H:i:s', $data['upload_date']),
                'gzz_xh' => $this->workStation->station_number,
                'bmbh' => $this->workStation->unit_number,
                'path' => $data['path'], //远程访问地址
                'wjsc' => $data['totalTime'],
                'wjzt' => $data['del_status'] == 3 ? 0 : 1,
                'bjlj' => Toolkit::getInformationFilePath($data),
                'wjbh' => Toolkit::getInformationFilePath($data),
            );
        }
        //print_r($_data);
        if (count($_data) > 0) {
            $url = Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp), "putInformations", $this->key);
            echo $url . "\n";
            //$_data = http_build_query($_data);
            $result = $this->exec_curl($url, http_build_query($_data), true);
            $result = json_decode($result, true);
            if ($result['code'] == 1) {
                if (isset($result['data']['suc_ids']) && count($result['data']['suc_ids']) > 0) {
                    Yii::app()->db->createCommand()->update('{{information}}', array('status' => 1), array('in', 'id', $result['data']['suc_ids']));
                }
                if (isset($result['data']['fail_ids']) && count($result['data']['fail_ids']) > 0) {
                    //上传索引失败的数据 status 改为2
                    Yii::app()->db->createCommand()->update('{{information}}', array('status' => 2), array('in', 'id', $result['data']['fail_ids']));
                }
            }
            //msg信息
            //print_r($result['msg']);
            //print_r($result);
        }
    }

    public function actionPutWsInfo() {
        $upload = Upload::model()->find();
        $workStation = Yii::app()->db->createCommand("select * from sdv_workstation  limit 1")->queryRow();
        if ($this->workStation->cloudIp <= 0) {
            return false;
        }
        $data = array(
            'ip' => sprintf("%u", ip2long(@gethostbyname($_ENV['COMPUTERNAME']))),
            'name' => $workStation['name'],
            'storage_size' => disk_total_space($this->config['params']['storage_driver_number']),
            'storage_rest' => disk_free_space($this->config['params']['storage_driver_number']),
            'memory_rate' => Toolkit::getMemoryRate(),
            'cpu_rate' => Toolkit::getCpuRate(),
            'mac_addr' => Toolkit::getMachineMac(),
            'client_version' => $workStation['client_version'],
            'server_version' => $workStation['server_version'],
            'address' => $workStation['address'],
            'manager' => $workStation['manager'],
            'phone' => $workStation['phone'],
            'station_number' => $workStation['station_number'],
            'unit_number' => $workStation['unit_number'],
            'type' => 1,
            'ftpIp' => sprintf("%u", ip2long($upload->ip)),
            'ftp_user' => $upload->name,
            'ftp_pass' => $upload->password,
            'cloudIp' => $workStation['cloudIp'],
            'merchant' => $workStation['merchant'],
        );
        //echo Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp),"putWSInfo",$this->key);
        $result = $this->exec_curl(Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp), "putWSInfo", $this->key), $data, true);
        $result = json_decode($result, true);
        echo "\nheartbeat:\n";
        print_r($result);
        if (isset($result['onlinedate'])) {
            Yii::app()->db->createCommand("update sdv_workstation set online_time='{$result['onlinedate']}'")->execute();
        }
        if ($result['data'] == 2) {
            if ($workStation['cloudIp'] > 0) {
                $url = Toolkit::getTclApiUrl(long2ip($workStation['cloudIp']), 'getUser', $this->key);
                $result = $this->exec_curl($url, array('dept_num' => $workStation['unit_number']));
                $result = json_decode($result, true);
                if ($result['code'] == 1 && is_array($result['data']) && count($result['data']) > 0) {
                    // 删除所有的用户
                    Yii::app()->db->createCommand("delete from sdv_user where id>1")->execute();
                    foreach ($result['data'] as $data) {
                        $users = array(
                            'id' => $data['id'],
                            'police_num' => $data['police_num'],
                            'password' => $data['password'],
                            'name' => $data['name'],
                            'sex' => $data['sex'],
                            'mobile_num' => $data['mobile_num'],
                            'role_id' => $data['role_id'],
                            'desc' => $data['desc'],
                            'created_date' => $data['created_date'],
                            'created_by' => $data['created_by'],
                            'status' => $data['status'],
                            'Number' => $data['Number']
                        );
                        Yii::app()->db->createCommand()->insert('{{user}}', $users);
                    }
                }
                //更新设备配对
                $this->actionGetMatcheByNumber();
                //更新权限部门信息
                $this->actionUpdatePermission();
                //在线升级
                $this->actionAutoUpgrade();
                //启动关机 和 重启
                $this->actionRestartShutdown();
                //启用 禁用
                $this->actionStationStatus();
            }
        }
        echo "\nheartbeat end -----------------------------\n";
    }

    /**
     * 获得根目录
     */
    public static function getRootPath() {
        return dirname(dirname(dirname(__FILE__)));
    }

            
}
