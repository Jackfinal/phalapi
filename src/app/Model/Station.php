<?php

namespace App\Model;

use PhalApi\Model\NotORMModel as NotORM;

class Station extends NotORM {


    protected function getTableName($id) {
        return 'stations';
    }

    public function addStation($station)
    {
        $stationCount = $this->getStationByNumber($station['station_number']);
        if($stationCount){
            $rs = $this->getORM()->where('station_number',$station['station_number'])->update($station);
        }else{
            $rs = $this->getORM()->insert($station);
        }
        return $rs;
    }
    
    public function getStationByNumber($station_number) {
        return $this->getORM()->select('*')->where('station_number', $station_number)->fetchOne();
    }

}
