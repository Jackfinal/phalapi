<?php
namespace App\Common;

use App\Common\Tools as CommonTools;

class Tools {

     /**
	 * 获取用户登录IP
	 *
	 * @param int $type 默认时取正常用户ip(这个ip可能被伪造),需要做ip屏蔽等功能时,需传参 type=1 来获取ip
	 *
	 * @return     string ip
	 */
	public static function getClientIp($type = 0)
	{
		if ($type == 1 && $_SERVER['REMOTE_ADDR']) {
			return $_SERVER['REMOTE_ADDR'];
		}
		$ip=false;
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip);
				$ip = false;
			}

			for ($i = 0; $i<count($ips); $i++) {
				if (!eregi("^(10|172\.16|127\.0|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
	public static function strstr($haystack ,$needle ,$before_needle= false)
	{
		if(strlen($haystack)<=0 || strlen($needle)<=0 || strpos($haystack,$needle)===false)
			return "";
		
		$result=strstr($haystack,$needle);
		return $before_needle? str_replace($result,"",$haystack) : $result;
	}

	public static function getHMS($second)
	{
		if($second<=0)
			return '00:00:00';
		
		$hour=(int)($second/3600);
		if($hour<10)
			$hour="0".$hour;
		$min=(int)(($second%3600)/60);
		if($min<10)
			$min='0'.$min;
		$second=$second%60;
		if($second<10)
			$second='0'.$second;
		return $hour.':'.$min.':'.$second;
	}
	
	/**
	 * @param information $information
	 * @return string
	 */
	public static function getInformationFilePath($information)
	{
		$path="";
		if(is_array($information) && count($information)>0){
			if (!empty($information['file_path'])) {
				$path = preg_replace("/^\/{$information['station_id']}/i", '', $information['file_path']);
				return $path;
			}
			$path = $information['police_num'].'/'.$information['equipment_num'] . '_' . date('YmdHis', $information['upload_date']) . '/' . $information['type'] . '/' . $information['file_name'];
			if (!empty($information['station_number']))
				$path = $information['station_number'] . '/' . $information['police_num'] . '/' . $information['equipment_num'] . '_' . date('YmdHis', $information['upload_date']) . '/' . $information['type'] . '/' . $information['file_name'];	 
			$path= '/'.$path;
		}
		else if(is_object($information) && get_class($information)=="Information"){
			if (!empty($information->file_path)) {
				$path = preg_replace("/^\/{$information->station_id}/i", '', $information->file_path);
				return $path;
			}
			$path = $information->police_num . '/' . $information->equipment_num . '_' . date('YmdHis', $information->upload_date) . '/' . $information->type . '/' . $information->file_name;
			if (!empty($information->station_number))
				$path = $information->station_number . '/' . $information->police_num . '/' . $information->equipment_num . '_' . date('YmdHis', $information->upload_date) . '/' . $information->type . '/' . $information->file_name;
			$path= '/'.$path;
		}
		
		return $path;
    }
	
        /**
     * 获取文件的绝对路径 --- 工作站使用多路径存储时(获取工作站的文件路径不准确)
     * 该方法现在还不适合放在for 循环体中 调用
     * @param $information
     * @return string
     */
    public static function getInformationFileRealPath($information) {
        //默认路径
        $dataPath = Yii::app()->params['file_server_root_path'] . Yii::app()->params['file_server_path'];
        $rs = array();
        if ($information['existed_file'] == '0') {
            $rs['isStation'] = 1;
            $station = Stations::getDataByStationNumber($information['station_id']);
            //文件未上传到平台的 --- 取工作站地址
            if ($station) {
                $ip = long2ip($station['ip']);
                $port = $this->file_server_port;
            }
            self::getInformationFilePath($information);
        } else {
            $rs['isStation'] = 0;
            $file_server_root_path = Yii::app()->params['file_server_root_path'];
            $file_server_path = Yii::app()->params['file_server_path'];
            $file_server_port = Yii::app()->params['file_server_port'];
            if (Yii::app()->params['is_multi_disk_storage'] && !empty($data['path'])) {
			$apache_ftp_path = $data['path'] . "/";
			$file_apache_ftp_path = Config::getConfigByType('apache_ftp_path');
			$dataPath = $file_apache_ftp_path[$data['path']]  . $file_server_path;
            } else {
                    $apache_ftp_path = "";
                    $dataPath = $file_server_root_path  . $file_server_path;
            }
            $station = Stations::getDataByStationNumber($information['station_id']);
            //上传到了存储服务器
            if ($station) {
                $ip = long2ip($station['ftpIp']);
                $port = $file_server_port;
            }
        }
        if (empty($ip)) {
            list($ip, $port) = explode(':', $_SERVER['HTTP_HOST']);
        }
        $port = empty($port) ? 80 : $port;
        $path = $dataPath ."/".$information['station_id']. self::getInformationFilePath($information);
        return array_merge(array('ip' => $ip, 'port' => $port, 'path' => $path), $rs);
    }

    public static function getMediaTimeLength($ffprobe,$media)
	{
		$length=0;
		$info=array();
		exec($ffprobe.' -i '.$media.' 2>&1',$info);
		
		if(!is_array($info) || count($info)<0)
			return $length;
		
		foreach($info as $d){
			$matches=array();
			if(!preg_match('/Duration:\s*(\d{1,2}):(\d{1,2}):(\d{1,2})/i',$d,$matches))
				continue;
				
			if(count($matches)!=4)
				continue;
			
			$length=$matches[1]*3600+$matches[2]*60+$matches[3];
			$length=intval($length);

			break;
		}
		
		return $length;
	}

	public static function getMemoryRate()
	{
		$pr=0;
		switch(strtolower(substr(PHP_OS,0,3))){
			case 'win':
				$path=dirname(dirname(dirname(__FILE__))).'/wintools/SystemsInfo.exe getMemoryUseInfo';
				$pr=exec($path);
				break;
			case 'lin':
				$str=shell_exec("more /proc/meminfo");
				$mode="/(.+):\s*([0-9]+)/";
				preg_match_all($mode,$str,$arr);
				$pr=Toolkit::bcdiv($arr[2][2],$arr[2][0],3);
				$pr=1-$pr;
				$pr=$pr*100;
				break;
			default:
				return false;
		}
		
		return $pr;
	}
	
	public static function getCpuRate()
	{
		$percent=0;
		switch(strtolower(substr(PHP_OS,0,3))){
			case 'win':
				$path=dirname(dirname(dirname(__FILE__))).'/wintools/SystemsInfo.exe getCPUInfo';
				$percent=exec($path);
				break;
			case 'lin':
				//计算CPU
				$mode = "/(cpu)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/";
				$string=shell_exec("more /proc/stat");
				preg_match_all($mode,$string,$arr);
				//print_r($arr);
				$total1=$arr[2][0]+$arr[3][0]+$arr[4][0]+$arr[5][0]+$arr[6][0]+$arr[7][0]+$arr[8][0]+$arr[9][0];
				$time1=$arr[2][0]+$arr[3][0]+$arr[4][0]+$arr[6][0]+$arr[7][0]+$arr[8][0]+$arr[9][0];
				 
				sleep(1);
				$string=shell_exec("more /proc/stat");
				preg_match_all($mode,$string,$arr);
				$total2=$arr[2][0]+$arr[3][0]+$arr[4][0]+$arr[5][0]+$arr[6][0]+$arr[7][0]+$arr[8][0]+$arr[9][0];
				$time2=$arr[2][0]+$arr[3][0]+$arr[4][0]+$arr[6][0]+$arr[7][0]+$arr[8][0]+$arr[9][0];
				$time=$time2-$time1;
				$total=$total2-$total1;
				//echo "CPU amount is: ".$num;

				$percent=Toolkit::bcdiv($time,$total,3);
				$percent=$percent*100;
				break;
			default:
				return false;
		}
		
		return $percent;
	}
	
	public static function bcdiv( $first, $second, $scale = 0 )
	{
		$res = $first / $second;
		return round( $res, $scale );
	}

	public static function getMachineMac()
	{
		$mac='';
		
		$result=array();
		switch(strtolower(substr(PHP_OS,0,3))){
			case 'win':
				exec("ipconfig /all",$result);
		 		break;
			case 'lin':
				exec("ifconfig",$result);
				break;
			default:
				return false;
		}
		
		if(!is_array($result) || count($result)<=0)
			return "";
			
		//var_dump($result);
		for($i=0;$i<count($result);$i++){
			$matches=array();
			if(preg_match("/([0-9a-f]{2}(?:[:-][0-9a-f]{2}){5})/i",$result[$i],$matches)){
            	$mac=$matches[1]; 
            	break;
			}
		}
		
		return str_replace('-',':',$mac);
	}
	
	public static function getVersion()
	{
		$path=dirname(dirname(dirname(__FILE__))).'/ver.txt';
		$fh=fopen($path,'r');
		if(!$fh)
			return false;

		$version=fread($fh, 100);
		fclose($fh);
		return $version;
	}
	
	public static function exec_curl($url,$data=array(),$isPost=true,$timeout=10)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($isPost){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		//curl_setopt($ch, CURLOPT_VERBOSE, true);
		$result = curl_exec($ch); # 得到的返回值
		curl_close($ch);
	
		return $result;
	}
	
	//制作授权密钥
	public static function getAuthKey($apiName,$key)
	{
		return $key;
	}
	
	public static function getNetAddress()
	{
		$data=array();
		
		switch(strtolower(substr(PHP_OS,0,3))){
			case 'win':
				$cmd_show = "chcp 437 | ipconfig -all";
				exec($cmd_show, $ret);				
				foreach($ret as $v){
					$v = iconv("gbk", "utf-8", $v);
					//匹配IP
					preg_match("/IPv4 Address(.|\s)*:\s(.*\d+)/", $v, $matches);
					if ($matches[1]) {
						isset($data["address"]) || $data["address"] = $matches[2];
						continue;
					}
					//匹配子网掩码
					preg_match("/Subnet Mask(.|\s)*:\s(.*)/", $v, $matches);
					if ($matches[1]) {
						isset($data["mask"]) || $data["mask"] = $matches[2];
						continue;
					}
					//匹配网关
					preg_match("/Default Gateway(.|\s)*:\s(.*)/", $v, $matches);
					if ($matches[1] && $matches[2]!='::') {
						isset($data["gateway"]) || $data["gateway"] = $matches[2];
						continue;
					}
					
					//匹配DNS
					preg_match("/DNS Servers(.|\s)*:\s(.*)/", $v, $matches);
					if ($matches[1]) {
						isset($data["DNS"]) || $data["DNS"] = $matches[2];
						continue;
					}
				}
				break;
			case 'lin':
				$cmd_show = "ifconfig|grep 'inet addr'";
				exec($cmd_show, $ret);
				
				preg_match('/inet addr:([0-9.]+)\s+.*Mask:([0-9.]+)/',$ret[0],$matches);
				$data['address']=$matches[1];
				$data['mask']=$matches[2];
				$data['gateway']=exec("route|grep 'default'|awk -F \" \" '{print $2}'");
				$data['DNS']=exec("cat /etc/resolv.conf|grep nameserver |awk -F \" \" '{print $2}'");
				break;
			default:
				break;
		}
		return $data;
	}
	
    public static function getInformationNum($data)
	{
        $time = md5(microtime());
		return 'tcl_'.$data['police_num'].$data['device_num'].$data['record_date'].'_'.$time;
	}
	
	public static function parsePriviVal($data)
	{
		$result=null;
		if(is_array($data)){
			$data['act_mdy']=(!array_key_exists("act_mdy", $data) || $data['act_mdy']!=1) ? 0:1;
			$data['act_del']=(!array_key_exists("act_del", $data) || $data['act_del']!=1) ? 0:1;
			$data['act_add']=(!array_key_exists("act_add", $data) || $data['act_add']!=1) ? 0:1;
			$data['act_exp']=(!array_key_exists("act_exp", $data) || $data['act_exp']!=1) ? 0:1;
			$data['act_imp']=(!array_key_exists("act_imp", $data) || $data['act_imp']!=1) ? 0:1;
			$data['act_dow']=(!array_key_exists("act_dow", $data) || $data['act_dow']!=1) ? 0:1;
			$data['act_upl']=(!array_key_exists("act_upl", $data) || $data['act_upl']!=1) ? 0:1;
			
			$result=$data['act_mdy'].$data['act_del'].$data['act_add'].$data['act_exp'].$data['act_imp'].$data['act_dow'].$data['act_upl'];
		}
		else if(strlen($data)==7){
			$arr=str_split($data);
			$result=array(
					'act_mdy'=>$arr[0],
					'act_del'=>$arr[1],
					'act_add'=>$arr[2],
					'act_exp'=>$arr[3],
					'act_imp'=>$arr[4],
					'act_dow'=>$arr[5],
					'act_upl'=>$arr[6]
			);
		}
		
		return $result;
	}
	
	/**
	 * 获得所有的子部门编号
	 * @param string $dept_num -1  表示取出所有的部门编号
	 * @return array()
	 */
	public static function getAllChildDeptId($dept_num)
	{
		if(strlen($dept_num)<=0)
			return array();

		$result=array();
		$condition="1 ";
		if($dept_num!=-1)
			$condition.=" and parent_number='$dept_num'";
		else
			$condition.=" ";
		
		$sql="select Number from sdv_unit where $condition";
		$data=Yii::app()->db->createCommand($sql)->queryAll();
		
		if(!is_array($data) || count($data)<=0)
			return $result;
		
		for($i=0;$i<count($data);$i++){
			
			array_push($result,$data[$i]['Number']);
			$result=array_merge($result,self::getAllChildDeptId($data[$i]['Number']));
		}
		return $result;
	}


    /**
     * 获取Session中的用户的操作权限
     * $type string  操作类型
     */
    public static function getUserAccess($type){
        switch($type){
            case 'mdy':
                $result = $_SESSION['user']['act_privi']['act_mdy'];
                break;
            case 'del':
                $result = $_SESSION['user']['act_privi']['act_del'];
                break;
            case 'add':
                $result = $_SESSION['user']['act_privi']['act_add'];
                break;
            case 'exp':
                $result = $_SESSION['user']['act_privi']['act_exp'];
                break;
            case 'imp':
                $result = $_SESSION['user']['act_privi']['act_imp'];
                break;
            case 'dow':
                $result = $_SESSION['user']['act_privi']['act_dow'];
                break;
            case 'upl':
                $result = $_SESSION['user']['act_privi']['act_upl'];
                break;
            default:
                $result = '0';

        }
        return $result;
    }
    
    public static function getAllAccessMunuId()
    {
    	$menus=Common::menuTreeConfig();
    	
    	$result=array();
    	foreach($menus as $menu){
    		$result[]=$menu[id];
    		if(!array_key_exists("children", $menu) || !is_array($menu['children']) || count($menu['children'])<=0)
    			continue;
    		
    		foreach($menu['children'] as $item)
    			$result[]=$item['id'];
    		
    	}

    	return $result;
    }
    
    /**
     * 更新用户的权限(部门为-99将是所有的数据(包括假数据), 部门为NULL表示自己的数据)
     * @param array $responce
     */
    public static function updateUserSessionPermission($responce)
    {
    	$permission = Permission::getDataByRoleId($responce['role_id']);
    	$act_privi=Toolkit::parsePriviVal('0000000');
    	$module=array();
    	$own_dept=array(-1);
    	
    	if($permission){
	    	//操作权限
	    	$act_privi=Toolkit::parsePriviVal($permission['act_info']);
	    	//访问权限
	    	$module = explode(",", $permission['module_ids']);
	    	//数据权限
	    	if ($permission['ws_extend']==2 && strlen($responce['Number'])>0) {
	    		if ($permission['check_data_type']==1) {
					//所有的部门数据权限
					$own_dept=Toolkit::getAllChildDeptId($responce['Number']);
				}
	    		array_push($own_dept, $responce['Number']);
				if ($permission['check_data_type']==2) {
					//所有部门的数据
					$own_dept=-99;
				}
	    	}else if($permission['ws_extend']==1){
	    		$own_dept=null;

	    	}
    	}

    	//系统管理员强制加上 config 模块权限 0.0 有点坑
    	if ($responce['police_num'] == 'system') {
			$own_dept=-99;//部门为零将是所有的数据(包括假数据)
			$module=Toolkit::getAllAccessMunuId();
			$act_privi=Toolkit::parsePriviVal("1111111");
			//系统管理员强制加上 config 模块权限 0.0 有点坑
			if (false === array_search('config', $module)) {
				$module[] = 'config';
			}
		} elseif ($responce['id'] == 1) {
			$own_dept=-99;
		}
    	$_SESSION['user'] = $responce;
    	$_SESSION['user']['module'] = array();
    	$_SESSION['user']['module'] = $module;
    	$_SESSION['user']['act_privi']=$act_privi;
    	$_SESSION['user']['own_dept']=$own_dept;
        //用户session超时设置
        yii::app()->user->setState('userSessionTimeout', time()+ yii::app()->params['sessionTimeoutSeconds']);
    }
    
    /**
     * @todo 下个版本  将添加厂家
     * @param string $cloudIp
     * @param string $requestName
     * @param string $key
     * @return string
     */
    public static function getTclApiUrl($cloudIp,$requestName,$key,$port='')
    {
        //web服务器端口的选择
        if(empty($port)){
            $port=Yii::app()->params['webPort'];
        }
    	return "http://$cloudIp:$port/TCLApi/$requestName?authKey=".self::getAuthKey($requestName,$key);
    }
    
    public static function execUpgrade($extra_file,$isClient,$upgrade_dir=null)
    {
    	$zip = new ZipArchive;
    	$res = $zip->open($extra_file);
    	if(!$res)
    		return false;
    	
    	$stationKey = $zip->getStream('Station-key.exe');
    	if ($isClient) {
    		if (empty($stationKey)) {
    			$zip->close();
    			@unlink($extra_file);
    			return false;
    		}
    		//杀死进程
    		exec("D:/pk/pk Station-key.exe");
    		@$zip->extractTo("D:/Station/R");
    		
    		exec("shutdown -r -t 0");
    	
    	} else {
    		if (!empty($stationKey)) {
    			$zip->close();
    			@unlink($extra_file);
    			return false;
    		}
    		
    		$dir=strlen($upgrade_dir)>0 ? $upgrade_dir : getcwd();
    		if(!@$zip->extractTo($dir)){
    			return false;
    		}
    	}
    	
    	$zip->close();
    	@unlink($extra_file);
    	
    	return true;
    }

	public static function log($levelStr, $msg, $logType = '', $date = "Y-m-d") {
		$file = ROOT_PATH . '/log/';
		if (!is_dir($file))
			mkdir($file);
		$logFileName = empty($logType) ? date($date) . '.log' : $logType . '_' . date($date) . '.log';
		$file .= $logFileName;
		$fp = fopen($file, 'a+');

		if (!$fp)
			return false;

		$old = umask(0);
		@chmod($file, 0777);
		umask($old);

		$content = date('Y-m-d H:i:s') . " [$levelStr] :" . $msg . PHP_EOL;
		fwrite($fp, $content);

		fclose($fp);
	}

	/**
	 * 建立文件夹
	 *
	 * @param string $aimUrl
	 * @return viod
	 */
	public static function createDir($dir) {
		$dir = str_replace('', '/', $dir);
		$aimDir = '';
		$arr = explode('/', $dir);
		$result = true;
		foreach ($arr as $str) {
			$aimDir .= $str . '/';
			if (!file_exists($aimDir)) {
				$result = mkdir($aimDir);
			}
		}
		return $result;
	}

	/**
	 * 目录删除   递归删除空目录
	 *
	 * @param $dir 目录path
	 *
	 * @return bool
	 */
	public static function delEmptyDir($dir)
	{
		$file_server_path = Yii::app()->params['file_server_path'];
		if (preg_match("/{$file_server_path}(\\\\|\/)?$/", $dir, $match)) {
			//文件存储根目录不进行删除
			return false;
		}
		if (!is_dir($dir)) {
			return false;
		}
		$flag = false;
		$scan = scandir($dir);
		if (count($scan) == 2) {
			//为2时表示空目录
			$flag = @rmdir($dir);
			$flag && self::delEmptyDir(dirname($dir));  //递归向上一级目录删除
		}
		return $flag;
	}

	/**
	 * 从数据集合中，获取某个字段的集合
	 * 如：比如将数据中的id提取出来
	 *
	 * @param array  $data        数据集合
	 * @param string $field       字段名
	 * @param bool   $unique      是否去重
	 * @param bool   $removeEmpty 是否去空（包括empty,null,0)
	 *
	 * @return void
	 */
	public function getFieldInList($data, $field="id", $unique=true, $removeEmpty=true)
	{
		$rs = array();
		foreach ($data as $k=>$v) {
			$val = $v[$field];
			if ($removeEmpty && !$val) {
				continue;
			}
			$rs[] = $v[$field];
		}

		if ($unique) {
			return array_values(array_unique($rs));
		} else {
			return $rs;
		}
	}

	/**
	 * 将数据集合的键值变成集合中的某个字段
	 *
	 * @param array  $data  集合
	 * @param string $field 字段
	 *
	 * @return type
	 */
	public function setDataAsKey($data, $field='id')
	{
		$rs = array();
		foreach ($data as $k=>$v) {
			$key = $v[$field];
			$rs[$key] = $v;
		}

		return $rs;
	}
    
    /**
	 *视频剪辑
	 *
	 *@param $ffmpeg 剪辑脚本路径
	 *@param $filename 原文件路径
	 *@param $start_time 开始位置时间 格式(00:00:00)
	 *@param $duration_time 持续时间  格式(00:00:00)
	 *@param $output  输出文件路径
	 */
	public static function clipVideo($ffmpeg,$filename,$start_time,$duration_time,$output)
	{
		if(strlen($ffmpeg)<=0 || strlen($filename)<=0 || strlen($output)<=0)
			return array(-1, "参数有误");
		//$cmd=ffmpeg -ss 00:01:20 -t 00:00:50 -i /data/1.avi -vcodec copy -acodec copy /data/1.test.avi
		
		//valid param		
		if(!file_exists($filename))
			return array(-2, "原文件不存在");

		if(strlen($start_time)<=0 || strlen($duration_time)<=0)
			return array(-3, "裁剪时间设置有误");
			
		$cmd="$ffmpeg -ss $start_time -t $duration_time -i $filename -vcodec copy -acodec copy -y $output 2>&1";
		$result=shell_exec($cmd);
		/******
		//bitrate=N/A speed=N/A 输出的比特率 速度都不为N/A的时候才算成功	
		if(!preg_match('/speed=\d{1,9}(?:\.\d{1,9}\+\d{2})?x/i',$result))
			return array(1, "剪辑成功");
		*****/
		//剪辑成功 的 判断 还有待完善
		return array(1, "剪辑完成");
	}
    
    /**
    * 加解密 
    * 
    * @param string $string    字符串
    * @param string $operation 操作方式 decode encode
    * @param string $key       密钥
    * @param string $expiry    ..... 
    *
    * @return .....
    **/
    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $auth_key      = !empty($key) ? $key : '';
        $key           = md5($auth_key);
        $key_length    = strlen($key);
        $string        = $operation == "DECODE" ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $string_length = strlen($string);
        $rndkey        = $box = array();
        $result        = "";
        $i             = 0;
        for (; $i <= 255; ++$i) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i]    = $i;
        }
        $j = $i = 0;
        for (; $i < 256; ++$i) {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        $a = $j = $i = 0;
        for (; $i < $string_length; ++$i) {
            $a       = ($a + 1) % 256;
            $j       = ($j + $box[$a]) % 256;
            $tmp     = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
        }
        if ($operation == "DECODE") {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return "";
            }
        } else {
            return str_replace("=", "", base64_encode($result));
        }
    }
    /**
     *
     * @return string win , lin
     */
    public static function getOsType()
    {
        return strtolower(substr(PHP_OS, 0, 3));
    }
    
    public static function getWavPlayUrl($information)
    {
        $file_server_port = Yii::app()->params['file_server_port'];
        $file_server_path = Yii::app()->params['file_server_path'];
        if ($information['existed_file'] == '0') {
            $station = Stations::getDataByStationNumber($information['station_id']);
            //文件未上传到平台的 --- 取工作站地址
            if (empty($station['ip'])) {
                return "";
            }
            $addr = long2ip($station['ip']);
            $url = "http://{$addr}:{$file_server_port}/index.php?r=resource/wav&archive_num={$information['archive_num']}";
        } else {
            $storage = Stations::getDataByStationNumber($information['station_id']);
            //print_r($storage);die;
            //多路径存储判断
            if (!empty($information['path'])) {
                $alias_path = '/' . $information['path'];
            } else {
                $alias_path = '/' . $file_server_path;
            }
            //取ftp服务器的IP地址  --  无ftp则取服务器地址
            if (empty($storage['ftpIp'])) {
                $path = "http://{$_SERVER['HTTP_HOST']}{$alias_path}/{$information['station_id']}"  . self::getInformationFilePath($information);
                $url = "http://{$_SERVER['HTTP_HOST']}/index.php?r=resource/wav&path=".urlencode($path);
            } else {
                $addr = long2ip($storage['ftpIp']);
                $path = "http://{$addr}{$alias_path}/{$information['station_id']}" . self::getInformationFilePath($information);
                $url = "http://{$addr}/index.php?r=resource/wav&path=".urlencode($path);
            }
        }
        return $url;
    }
    
}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         