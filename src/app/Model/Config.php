<?php

/**
 * This is the model class for table "{{memory}}".
 *
 * The followings are the available columns in table '{{memory}}':
 * @property integer $id
 * @property integer $memory_cycle
 * @property integer $created_date
 * @property integer $cteated_by
 */
class Config extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Memory the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
     public static $tableName="{{config}}";

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{config}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, value, state', 'update_time', 'remark'),
            array('name, value, state', 'update_time', 'remark'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('name, value, state', 'update_time', 'remark', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'name' => 'name',
            'value' => 'value',
            'state' => 'state',
			'update_time' => 'update_time',
            'remark' => 'remark',
			
        );
    }


	/**
	 *配置初始化到全局变量中
	 *
	 */
    public static function configInit() {
        $configs = self::getConfigByType('system');
		foreach($configs as $key => $val) {
			Yii::app()->params[$key] = $val;
		}
		return true;
    }

    /**
     * 获取启用的指定配置类型的所有启用的配置信息
     * @param string $configType 配置类型
     * @return array
     */
    public static function getConfigByType($configType = '')
    {
        if (empty($configType)) {
            return array();
        }
        $configArr = Yii::app()->db->createCommand()
            ->select("name, value")
            ->from(self::$tableName)
            ->where("state = 1 and config_type='{$configType}'")
            ->queryAll();
        $configs = array();
        //配置信息转为一维数据
        foreach($configArr as $key => $val) {
            $configs[$val['name']] = $val['value'];
        }
        return $configs;
    }


    /**
     *配置类型说明
     */
    public static function configType()
    {
        return array(
            //系统级配置会在控制器初始化时被加载，若配置文件中书定义的配置名，会被重载，使用Yii::app()->params['config_name'] 调用
            'system' => '系统级配置',
            //多路径存储配置-这里配置存储的路径
            'apache_ftp_path' => '多路径存储配置',
            //....有需要可自行添加其他配置类型
        );
    }

    /**
     * 获取数据库中所有配置类型
     * @param int $type   0：返回配置表中所有配置类型列表，1：返回配置表中所有配置类型说明列表, 2:返回包括配置类型说明的所有配置类型说明列表
     * @return array
     */
    public static function getConfigType($type = 0)
    {
        $configType = array();
        $data = Yii::app()->db->createCommand()
            ->select("config_type")
            ->from(self::$tableName)
            ->group('config_type')
            ->queryAll();
        $types = self::configType();
        foreach ($data as $key => $val) {
            if ($type == 1 || $type == 2) {
                $configType[$val['config_type']] = isset($types[$val['config_type']]) ? $types[$val['config_type']] : $val['config_type'];
            } else {
                $configType[] = $val['config_type'];
            }
        }
        if ($type == 2) {
            $configType = $types + $configType;
        }
        return $configType;
    }




    /**
     * 获取条数
     * @param $condition
     * @return mixed
     */
    public static function getCount($condition)
    {
        $_where = "1 = 1";
        $where = array();
        foreach ($condition['where'] as $key => $val) {
            //数值为空（0除外）全部排除
            if (!empty($val) || $val === 0) {
                $_where .= " and {$key} = :{$key}";
                $where[":" . $key] = $val;
            }
        }
        $data = Yii::app()->db->createCommand()
            ->select("count(id) as count")
            ->from(self::$tableName)
            ->where($_where, $where)
            ->queryRow();
        return $data['count'];
    }
    /**
     * 获取列表
     * @param $condition
     * @return mixed
     */
    public static function getDataList($condition)
    {
        $_where = "1 = 1";
        $where = array();
        foreach ($condition['where'] as $key => $val) {
            //数值为空全部排除
            if (!empty($val) || $val === 0) {
                $_where .= " and {$key} = :{$key}";
                $where[":" . $key] = $val;
            }
        }
        $data = Yii::app()->db->createCommand()
            ->select("*")
            ->from(self::$tableName)
            ->where($_where, $where)
            ->limit($condition['limit']['limit'], $condition['limit']['start'])
            ->order($condition['order'])
            ->queryAll();
        return $data;
    }

    /**
     * 通过id 或者配置名称 取此条配置信息
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public static function getConfigByKey($id = 0, $name = "")
    {
        if (empty($id) && empty($name)) {
            return array();
        }
        $_where = "1 = 1";
        $where = array();
        foreach (array('id' => $id, 'name' => $name) as $key => $val) {
            //数值为空全部排除
            if (!empty($val)) {
                $_where .= " and {$key} = :{$key}";
                $where[":" . $key] = $val;
            }
        }
        $data = Yii::app()->db->createCommand()
            ->select("*")
            ->from(self::$tableName)
            ->where($_where, $where)
            ->queryRow();
        return $data;
    }

    /**
     * 新增配置数据
     * @param $data
     * @return mixed
     */
    public static function add($data)
    {
        return Yii::app()->db->createCommand()->insert(self::$tableName, $data);
    }

    /**
     * 更新配置数据
     * @param $condition
     * @param $data
     * @return bool
     */
    public static function updateConfig($condition, $data)
    {
        if (empty($condition)) {
            return false;
        }
        $_where = "1 = 1";
        $where = array();
        foreach ($condition['where'] as $key => $val) {
            //数值为空全部排除
            if (!empty($val) || $val === 0) {
                $_where .= " and {$key} = :{$key}";
                $where[":" . $key] = $val;
            }
        }

        return Yii::app()->db->createCommand()->update(self::$tableName, $data, $_where, $where);
    }
}