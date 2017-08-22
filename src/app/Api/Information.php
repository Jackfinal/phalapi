<?php
namespace App\Api;

use PhalApi\Api;
use App\Domain\Information as Domain;
use PhalApi\Exception\BadRequestException;
use PhalApi\Request\Parser;

/**
 * 数据管理接口服务类
 *
 */

class Information extends Api {

    public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username', 'default' => 'PHPer', ),
            ),
            'update' => array(
                'archive_num'  => array('name' => 'wjbh', 'require' => true, 'min' => 1, 'max' => '100', 'desc'=> '通过文件编号更新数据'),
                'status' 	   => array('name' => 'sjzt', 'type' => 'enum', 'range' => array(0, 1), 'desc'=> '数据状态'),
                'existed_file' => array('name' => 'sczt', 'type' => 'enum', 'range' => array(0, 1), 'desc'=> '上传状态'),
                'level'        => array('name' => 'zybj', 'type' => 'enum', 'range' => array(1, 2, 3), 'desc'=> '执法仪重要标记文件，1：不重要，2: 中等, 3：重要文件'),
                'del_status'   => array('name' => 'wjzt', 'type' => 'enum', 'range' => array(0, 1), 'desc'=> '文件删除状态，0：未删除，1：已删除'),
                'play_path'    => array('name' => 'bflj', 'min' => 1, 'max' => '255', 'desc'=> '可播放的HTTP路径'),
                'download_path'    => array('name' => 'xzlj', 'min' => 1, 'max' => '255', 'desc'=> '可下载的HTTP路径'),
            ),
        );
    }


    /**
     * puts接口规则
     * @return array
     */
    protected function putsRules()
    {
        return array(
            'id' => array('name' => 'id', 'require' => true, 'max' => '11', 'desc'=> '主键id'),
            'wjbh' => array('name' => 'wjbh', 'require' => true, 'min' => 0, 'max' => '100',
                'desc'=> '文件编号:为该索引的全球唯一编号,且保证通过此编号能够找到原索引,若为空系统会自动创建并返回'),
            'wjmc' => array('name' => 'wjmc', 'require' => true, 'min' => 1, 'max' => '255', 'desc'=> '文件名称'),
            'bmbh' => array('name' => 'bmbh', 'require' => true, 'min' => 1, 'max' => '30', 'desc'=> '部门编号'),
            'gzz_xh' => array('name' => 'gzz_xh', 'require' => true, 'min' => 1, 'max' => '30', 'desc'=> '采集工作站编号'),
            'sbbh' 	 => array('name' => 'sbbh', 'require' => true, 'min' => 1, 'max' => '30', 'desc'=> '执法记录仪编号'),
			'sjzt' 	 => array('name' => 'sjzt', 'type' => 'enum', 'range' => array(0, 1), 'require' => true, 'desc'=> '数据状态'),
			'sczt' 	 => array('name' => 'sczt', 'type' => 'enum', 'range' => array(0, 1), 'require' => true, 'desc'=> '上传状态'),
			'ccbs' 	 => array('name' => 'ccbs', 'desc'=> '存储标识'),
            'yhbh' 	 => array('name' => 'yhbh', 'require' => true, 'min' => 1, 'max' => '50', 'desc'=> '用户编号'),
            'pssj' 	 => array('name' => 'pssj', 'type' => 'date', 'require' => true, 'format' => 'timestamp', 'desc'=> '拍摄时间'),
            'drsj' 	 => array('name' => 'drsj', 'type' => 'date', 'require' => true, 'format' => 'timestamp', 'desc'=> '导入时间'),
            'mtlx' 	 => array('name' => 'mtlx', 'type' => 'enum', 'range' => array(0, 1, 2, 3), 'require' => true, 'desc'=> '媒体类型:1 视频, 2 音频,3 图片,4 文本'),
            'wjgs' 	 => array('name' => 'wjgs', 'desc'=> '文件格式，如MP4'),
            'wjdx'   => array('name' => 'wjdx', 'type' => 'int', 'require' => true, 'desc'=> '文件大小，单位Byte'),
            'zybj' 	 => array('name' => 'zybj', 'type' => 'enum', 'range' => array(1, 2, 3), 'require' => true, 'desc'=> '执法仪重点标记文件，1：不重要，2: 中等, 3：重要文件'),
            'wjsc' 	 => array('name' => 'wjsc', 'type' => 'int', 'require' => true, 'desc'=> '视频时长，单位秒；若图片则为0'),
            'wjzt' 	 => array('name' => 'wjzt', 'type' => 'enum', 'range' => array(0, 1), 'require' => true, 'desc'=> '文件删除状态，0：未删除，1：已删除'),
            'bflj'  => array('name' => 'bflj', 'require' => true, 'min' => 1, 'max' => '255', 'desc'=> '可播放的HTTP路径'),
            'xzlj' 	 => array('name' => 'xzlj', 'require' => true, 'min' => 1, 'max' => '255', 'desc'=> '可下载的HTTP路径'),
        );
    }

    /**
     * 取接口参数规则
     *
     * 主要包括有：
     * - 1、[固定]系统级的service参数
     * - 2、应用级统一接口参数规则，在app.apiCommonRules中配置
     * - 3、接口级通常参数规则，在子类的*中配置
     * - 4、接口级当前操作参数规则
     *
     * <b>当规则有冲突时，以后面为准。另外，被请求的函数名和配置的下标都转成小写再进行匹配。</b>
     *
     * @uses Api::getRules()
     * @return array
     */
    public function getApiRules() {
        $rules = array();

        $allRules = $this->getRules();
        if (!is_array($allRules)) {
            $allRules = array();
        }
        $allRules = array_change_key_case($allRules, CASE_LOWER);

        $action = strtolower(\PhalApi\DI()->request->getServiceAction());
        if (isset($allRules[$action]) && is_array($allRules[$action])) {
            $rules = $allRules[$action];
        }


        if (isset($allRules['*'])) {
            $rules = array_merge($allRules['*'], $rules);
        }

        $apiCommonRules = \PhalApi\DI()->config->get('app.apiCommonRules', array());
        if (!empty($apiCommonRules) && is_array($apiCommonRules)) {
            $rules = array_merge($apiCommonRules, $rules);
        }
        if (preg_match('/docs\.php$/', $_SERVER['SCRIPT_FILENAME'])) {
            $method = $action . 'Rules';
            if (method_exists($this, $method)) {
                $rules = $this->$method();
            }
        }
        return $rules;
    }

    /**
    * 默认接口服务
    * @desc 默认接口服务，当未指定接口服务时执行此接口服务
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function index() {
        return array(
            'title' => 'Hello World!',
            'content' => \PhalApi\T('Hi {name}, welcome to use PhalApi!', array('name' => $this->username)),
            'version' => PHALAPI_VERSION,
            'time' => $_SERVER['REQUEST_TIME'],
        );
    }
    /**
    * 批量上传数据索引
    * @desc 批量上传数据索引(不检查文件)
    * @return string title 标题
    * @return string content 内容
    * @return string version 版本，格式：X.X.X
    * @return int time 当前时间戳
    */
    public function fileInfoUpload()
    {
            $data = $_POST;
            $rs = array();
            $unit_numbers = array();  //警员的部门编号
            $this->data = array(
                    'suc_ids' => array(),
                    'fail_ids' => array(),
            );
            $domain = new DomainInformatin();
            $id = $domain->fileInfoUpload($data);
            $rs['id'] = $id;  
            return ;
    }
        
    /**
    *修改上传文件状态
     * @desc 用于更新文件状态
    */
    public function actionUpdateFileStatus()
    {
        $archive_num = $_POST['archive_num'];
        $sql = "update sdv_information set status=1 , existed_file=1 where archive_num='{$archive_num}'";
        $result = Yii::app()->db->createCommand($sql)->execute();
        $this->code = 1;
        $this->data = $sql;
    }
    
    /**
    *删除上报接口
     * @desc 更新总队删除状态
    **/
    public function actionNotifyDeleteFile()
    {
        $archive_num = $_POST['archive_num'];
        //echo $archive_num;
        //file_put_contents('i1.php',var_export("update sdv_information set del_status=1 where archive_num='{$archive_num}'",true),FILE_APPEND);
        if(strstr($archive_num,","))
        {
            $archive_num = "'".str_replace(",","','",$archive_num)."'";
        }
        else
        {
            $archive_num = "'".$archive_num."'";
        }
        if($archive_num)
        {
            $result = Yii::app()->db->createCommand("update sdv_information set del_status=1 where archive_num in ({$archive_num}) and existed_file <> 1")->execute();
            $this->code = 1;
        }
    }


    /**
     * 批量上传索引接口
     * @desc 批量上传索引,数据批量入库;通过POST_ROWS 方式 POST 索引列表进,每一个列表都是为包含下列参数的map数组
     * @return array suc_ids     成功入库的数据 {id : wjbh}
     * @return array existed_ids 通过文件编号(wjbh)检查已经存在的数据,不进行入库 {id : wjbh}
     * @return array fail_ids    入库失败的数据 {id : wjbh}
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
        $data = array();
        foreach ($_data as $key => $val) {
            $data[$key] = array(
                'id' => $val['id'],
                'archive_num'   => $val['wjbh'],
                'police_num'    => $val['yhbh'],
                'equipment_num' => $val['sbbh'],
                'file_name'     => $val['wjmc'],
                'size'          => $val['wjdx'],
				'status'        => $val['sjzt'],
				'existed_file'  => $val['sczt'],
				'path'  => $val['ccbs'],
                'type'          => $types[$val['mtlx']],
                'level'         => $val['zybj'],
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

    /**
     * 更新数据索引信息接口
     * @desc 通过文件编号(wjbh)更新数据索引信息
     * @return int - 更新成功数据条数
     */
    public function update()
    {
        $rules = $this->getRules();
        $rules = $rules['update'];
        $keys = array_keys($rules);
        $data = array();
        foreach ($keys as $key) {
            if ($this->$key !== null) {
                $data[$key] = $this->$key;
            }
        }
        $domain = new Domain();
        return $domain->updateByArchiveNum($data);

    }

}
