<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
defined('ROOT_PATH') or define('ROOT_PATH', 'D:\www\sdv');
require_once(ROOT_PATH . "/sdk/PhalApiClient/PhalApiClient.php");

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
    protected $clientApi;  // 接口实例
    protected $clientApiDebugParam = '&debug=1&__debug__=1';

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
        $api_url = 'http://' . $this->cloudIp . '/api/public/';
        //$api_url = 'http://dev.phalapi.com/';
        $this->clientApi = PhalApiClient::create()->withHost($api_url);
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
            //$this->actionNotice();
        }
    }

    //获取分类列表
    public function getCategoryList() {
        $cloudIp = long2ip($this->workStation->cloudIp);
        $rs = $this->clientApi->reset()
            ->withService('Category.getCategoryList')
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();
        if ($rs->getRet() == 200 && is_array($rsdata['items']) && count($rsdata['items']) > 0) {
            foreach ($rsdata['items'] as $data) {
                $category = array(
                    'id' => $data['flid'],
                    'pid' => $data['sjfl'],
                    'cate_name' => $data['flmc'],
                    'del_status' => $data['flzt'],
                    'storage_time' => $data['ccsj']
                );
                $cateCount = Category::getByKey($data['flid']);
                if (empty($cateCount)) {
                    Yii::app()->db->createCommand()->insert('{{category}}', $category);
                } else {
                    Yii::app()->db->createCommand()->update('{{category}}', $category, 'id=:id', array(':flid' => $data['flid']));
                }
            }
        }
    }

    //获取用户列表
    public function getUserList() {
        $cloudIp = long2ip($this->workStation->cloudIp);
        $unit_number = $this->workStation->unit_number;
        $rs = $this->clientApi->reset()
            ->withService('User.getUserList')
            ->withParams('bmbh', $unit_number)
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();

        if ($rs->getRet() == 200 && is_array($rsdata['items']) && count($rsdata['items']) > 0) {
            foreach ($rsdata['items'] as $data) {
                $users = array(
                    'police_num' => $data['yhbh'],
                    'password' => $data['yhmm'],
                    'name' => $data['yhxm'] . 'ddd',
                    'sex' => $data['yhxb'],
                    'mobile_num' => $data['dhhm'],
                    'role_id' => $data['yhjs'],
                    'desc' => "",
                    'created_date' => "",
                    'created_by' => "",
                    'status' => $data['yhzt'],
                    'Number' => $data['bmbh']
                );
                $userCount = User::getUserByPoliceNum($data['yhbh']);
                if (empty($userCount)) {
                    $user_result = Yii::app()->db->createCommand()->insert('{{user}}', $users);
                } else {
                    $user_result = Yii::app()->db->createCommand()->update('{{user}}', $users, 'police_num=:police_num', array(':police_num' => $data['yhbh']));
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
                $this->getCategoryList();
                //更新权限部门信息
                /* $this->actionUpdatePermission();
                  $this->actionGetMatcheByNumber();
                  $this->actionPutinformations(2); */
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

    //获取部门列表
    public function getUnitList() {
        $cloudIp = long2ip($this->workStation->cloudIp);
        $unit_number = $this->workStation->unit_number;
        $rs = $this->clientApi->reset()
            ->withService('Unit.getUnitList')
            ->withParams('bmbh', $unit_number)
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();
        if ($rs->getRet() == 200 && is_array($rsdata) && count($rsdata) > 0) {
            foreach ($rsdata['items'] as $data) {
                $unit = array(
                    'Name' => $data['bmmc'],
                    'Number' => $data['bmbh'],
                    'Desc' => "",
                    'parent_number' => $data['sjbm'],
                    'contact' => $data['lxr'],
                    'phone' => $data['lxdh'],
                    'created_date' => "",
                    'child_count' => ""
                );
                $unitCount = Unit::getUnitNameByNumber($data['bmbh']);
                if (empty($unitCount)) {
                    Yii::app()->db->createCommand()->insert('{{unit}}', $unit);
                } else {
                    Yii::app()->db->createCommand()->update('{{unit}}', $unit, 'Number=:Number', array(':Number' => $data['bmbh']));
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

        if ($status == 0) {
            $where = "status2 = 0 ";
        } elseif ($status == 2) {
            $e_time = 30 * 86400; //7天之前上传失败的不再次上传
            $where = "status2 = 2 and upload_date >= {$e_time}";
        }
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
            'photo' => 3
        );
        $playUrls = Toolkit::getPlayUrls($datas);
        $downlaodUrls = Toolkit::getDownloadUrls($datas);
        foreach ($datas as $key => $data) {
            $_data[$key] = array(
                'id' => $data['id'],
                'wjbh' => $data['archive_num'],
                'yhbh' => $data['police_num'],
                'sbbh' => $data['equipment_num'],
                'wjmc' => $data['file_name'],
                'wjdx' => $data['size'],
                'sjzt' => $data['status'],
                'sczt' => $data['existed_file'],
                'ccbs' => $data['path'],
                'mtlx' => $types[$data['type']],
                'zybj' => $data['level'],
                'pssj' => date('Y-m-d H:i:s', $data['record_date']),
                'drsj' => date('Y-m-d H:i:s', $data['upload_date']),
                'gzz_xh' => $data['station_id'],
                'bmbh' => $data['unit_number'],
                'path' => $data['path'], //远程访问地址
                'wjsc' => $data['totalTime'],
                'wjzt' => $data['del_status'] == 3 ? 1 : 0,
                'bflj' => $playUrls[$data['id']], //播放路径
                'xzlj' => $downlaodUrls[$data['id']], //下载路径
            );
        }
        //print_r($_data);die();

        if (count($_data) > 0) {
            $rs = $this->clientApi->reset()
                ->withService('Information.Puts')
                ->setParams(http_build_query($_data))
                ->withTimeout(15000)
                ->request();

            if ($rs->isSuccess()) {
                $result = $rs->getData();
                $sucIdsStr = '';
                if (isset($result['suc_ids']) && count($result['suc_ids']) > 0) {
                    $sucIds = array_keys($result['suc_ids']);
                    //Yii::app()->db->createCommand()->update('{{information}}', array('status2' => 1), array('in', 'id', $sucIds));
                    $sucIdsStr = implode(',', $sucIds);
                }
                $failIdsStr = '';
                if (isset($result['fail_ids']) && count($result['fail_ids']) > 0) {
                    //上传索引失败的数据 status 改为2
                    $failIds = array_keys($result['suc_ids']);
                    //Yii::app()->db->createCommand()->update('{{information}}', array('status2' => 2), array('in', 'id', $failIds));
                    $failIdsStr = implode(',', $failIds);
                }
                $existedIdsStr = '';
                if (isset($result['existed_ids']) && count($result['existed_ids']) > 0) {
                    //已经存在也表示 入库成功, 因为archive_num保证唯一
                    $existedIds = array_keys($result['existed_ids']);
                    Yii::app()->db->createCommand()->update('{{information}}', array('status2' => 1), array('in', 'id', $existedIds));
                    $existedIdsStr = implode(',', $existedIds);
                }
                empty($sucIdsStr) || Toolkit::log('info', "Information.Puts suc_ids {$sucIdsStr} ", 'Api');
                empty($failIdsStr) || Toolkit::log('error', "Information.Puts fail_ids {$failIdsStr} ", 'Api');
                empty($existedIdsStr) || Toolkit::log('warring', "Information.Puts existed_ids {$existedIdsStr} ", 'Api');
            } else {
                $ret = $rs->getRet();
                $errMsg = $rs->getMsg();
                if (is_array($errMsg)) {
                    $errMsg = json_decode($errMsg, true);
                }
                Toolkit::log('error', "Information.Puts {$ret} {$errMsg} ", 'Api');
            }
            /*
              echo "\n";
              var_dump($rs->getRet());
              echo "\n";
              print_r($rs->getData());
              var_dump($rs->getMsg());
              echo "\n";
             */
        }
    }

    public function actionPutWsInfo() {
        $upload = Upload::model()->find();
        $cloudIp = long2ip($this->workStation->cloudIp);
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
        $stationInfo = json_encode($data);
        print_r($stationInfo);
        //echo Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp),"putWSInfo",$this->key);
        $rs = $this->clientApi->reset()
            ->withService('Station.stationInfoUpload')
            ->withParams('stationInfo', $stationInfo)
            //->setParams(http_build_query($data))
            ->withTimeout(3000)
            ->request();
        $rsdata = $rs->getData();
        print_r($rsdata);
    }

    /**
     * 获得根目录
     */
    public static function getRootPath() {
        return dirname(dirname(dirname(__FILE__)));
    }

    public function actionPutStations() {
        $upload = Upload::model()->find();
        $cloudIp = long2ip($this->workStation->cloudIp);
        $upload = Upload::model()->find();
        $workStation = Yii::app()->db->createCommand("select * from sdv_workstation  limit 1")->queryRow();
        if ($workStation['cloudIp'] <= 0) {
            return false;
        }
        $stations = Yii::app()->db->createCommand("select * from sdv_stations")->queryAll();
        foreach ($stations as $station) {
            $data = array(
                'ip' => $station['ip'],
                'name' => $station['name'],
                'storage_size' => $station['storage_size'],
                'storage_rest' => $station['storage_rest'],
                'memory_rate' => $station['memory_rate'],
                'cpu_rate' => $station['cpu_rate'],
                'mac_addr' => $station['mac_addr'],
                'client_version' => $station['client_version'],
                'server_version' => $station['server_version'],
                'address' => $station['address'],
                'manager' => $station['manager'],
                'phone' => $station['phone'],
                'station_number' => $station['station_number'],
                'unit_number' => $station['unit_number'],
                'type' => $station['type'],
                'ftpIp' => $station['ftpIp'],
                'ftp_user' => $station['ftp_user'],
                'ftp_pass' => $station['ftp_pass'],
                'created_date' => $station['created_date'],
                'online_date' => $station['online_date'],
                'merchant' => $station['merchant'],
            );
            $stationInfo = json_encode($data);
            print_r($stationInfo);
            //echo Toolkit::getTclApiUrl(long2ip($this->workStation->cloudIp),"putWSInfo",$this->key);
            $rs = $this->clientApi->reset()
                ->withService('Station.stationInfoUpload')
                ->withParams('stationInfo', $stationInfo)
                //->setParams(http_build_query($data))
                ->withTimeout(3000)
                ->request();
            $rsdata = $rs->getData();
            print_r($rsdata);
        }
    }

}
