<?php

/**
 * This is the model class for table "{{stations}}".
 *
 * The followings are the available columns in table '{{stations}}':
 * @property integer $id
 * @property string $name
 * @property integer $ip
 * @property integer $created_date
 * @property integer $created_by
 */
class Stations extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Stations the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
     public static $tableName="{{stations}}";

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{stations}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, ip, created_date, created_by', 'required'),
            array('id, ip, created_date, created_by', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 255),
            array('unit_number','length','max'=>40),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, ip, created_date, created_by', 'safe', 'on' => 'search'),
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
            'name' => 'Name',
            'ip' => 'Ip',
            'created_date' => 'Created Date',
            'created_by' => 'Created By',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('ip', $this->ip);
        $criteria->compare('created_date', $this->created_date);
        $criteria->compare('created_by', $this->created_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * 必须拥有管理员的权限,才能查看数据
     * @param array $condition
     * @return int
     */
    public function getCount($condition = array()) {

        $sql = "select count(*) as count from " . self::$tableName ." s LEFT JOIN {{unit}} u ON s.unit_number=u.Number where 1 ";
        if(array_key_exists("name", $condition['where']) && strlen($condition['where']['name'])>0)
            $sql .= " and s.name like binary('%{$condition['where']['name']}%')";
		if(array_key_exists("own_dept", $condition['where']) && $condition['where']['own_dept']!=null){
			if($condition['where']['own_dept']!=-99)
            	$sql .= " and s.unit_number in ('".implode("','",$condition['where']['own_dept'])."')";
		}else
			$sql.=" and s.unit_number in ('-1') ";

        $responce = Yii::app()->db->createCommand($sql)->queryRow();


        return $responce['count'];
    }

    /**
     * 必须拥有管理员的权限,才能查看数据
     * @param array $condition
     * 
     */
    public function getDataList(array $condition) {
        $sql = "select s.*,u.Name as unit_name from " . self::$tableName ." s LEFT JOIN {{unit}} u ON s.unit_number=u.Number where 1 ";
        if(array_key_exists("name", $condition['where']) && strlen($condition['where']['name'])>0)
            $sql .= " and s.name like binary('%{$condition['where']['name']}%')";
		if(array_key_exists("own_dept", $condition['where']) && $condition['where']['own_dept']!=null){
			if($condition['where']['own_dept']!=-99)
            	$sql .= " and s.unit_number in ('".implode("','",$condition['where']['own_dept'])."')";
		}else
			$sql.=" and s.unit_number in ('-1') ";
		
        $sql .= " order by s.{$condition['order']['sidx']} {$condition['order']['sord']}";
        $sql .= " limit {$condition['limit']['start']},{$condition['limit']['limit']}";

        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    public static function getDataById($id) {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from(self::$tableName)
            ->where('id=:id', array(':id' => $id))
            ->queryRow();
    }
    public static function getDataByStationNumber($station_number){
        return Yii::app()->db->createCommand()
            ->select('id,name,ip,station_number,ftpIp,ftp_user,ftp_pass')
            ->from(self::$tableName)
            ->where('station_number=:station_number', array(':station_number' => $station_number))
            ->queryRow();
    }
    /**
     * 统计工作站的数量，可用作判断本地是否二级服务器
     *
     * @return int
     */
    public function getStationNumber() {

        $sql = "select count(*) as count from " . self::$tableName ;
        //print_r($_SESSION);exit;
        $responce = Yii::app()->db->createCommand($sql)->queryRow();

        return $responce['count'];
    }

    /**
     * 获取所有站点的ftp帐号
     */
    public static function getStationFtpAll()
    {
        $responce = Yii::app()->db->createCommand()
            ->from(self::$tableName)
            ->select("station_number, ftp_user")
            ->queryAll();
        $data = array();
        foreach ($responce as $key => $val) {
            $data[$val['station_number']] = $val['ftp_user'];
        }
        return $data;
    }


}
