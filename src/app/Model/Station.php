<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Station extends NotORM {


    protected function getTableName($id) {
        return 'stations';
    }

    public function inserta($information)
    {
        $stationCount = $this->getStationByNumber($information['station_number']);
        if($stationCount){
            $rs = $this->getORM()->where('station_number',$information['station_number'])->update($information);
        }else{
            $rs = $this->getORM()->insert($information);
        }
         
        
        return $rs;
    }
    public function getStationByNumber($station_number) {
        return $this->getORM()->select('*')->where('station_number', $station_number)->fetchOne();
    }

}
