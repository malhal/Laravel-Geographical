<?php
/**
 *  Laravel-Geographical (http://github.com/malhal/Laravel-Geographical)
 *
 *  Created by Malcolm Hall on 4/10/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\Geographical;

trait Geographical
{
    public function newDistanceQuery($lat, $lon, $kilometers = false){

        $unit = $kilometers ? 'kilometers' : 'miles';

        $query = $this->newQuery();

        $latName = $this->getQualifiedLatitudeColumn();
        $lonName = $this->getQualifiedLongitudeColumn();

        $query->select($this->getTable() . '.*');

        $sql = "((ACOS(SIN(? * PI() / 180) * SIN(" . $latName . " * PI() / 180) + COS(? * PI() / 180) * COS(" . $latName . " * PI() / 180) * COS((? - " . $lonName . ") * PI() / 180)) * 180 / PI()) * 60 * ?) as " . $unit;

        if($kilometers){
            $query->selectRaw($sql, [$lat, $lat, $lon, 1.1515 * 1.609344]);
        }
        else{
            // miles
            $query->selectRaw($sql, [$lat, $lat, $lon, 1.1515]);
        }

        //echo $query->toSql();
        //var_export($query->getBindings());

        return $query;
    }

    protected function getQualifiedLatitudeColumn(){
        return $this->getTable().'.'.$this->getLatitudeColumn();
    }

    protected function getQualifiedLongitudeColumn(){
        return $this->getTable().'.'.$this->getLongitudeColumn();
    }

    public function getLatitudeColumn()
    {
        return defined('static::LATITUDE') ? static::LATITUDE : 'latitude';
    }

    public function getLongitudeColumn()
    {
        return defined('static::LONGITUDE') ? static::LONGITUDE : 'longitude';
    }
}

?>