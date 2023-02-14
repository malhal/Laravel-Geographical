<?php

namespace Malhal\Geographical;

use Illuminate\Database\Eloquent\Builder;

/**
 * Geographical trait
 * Laravel-Geographical (http://github.com/malhal/Laravel-Geographical)
 *
 * @const string LATITUDE If the latitude column name if different than 'latitude'.
 * @const string LONGITUDE If the latitude column name if different than 'longitude'.
 *
 * @property boolean $kilometers Set to true if metric unit is needed.
 * @copyright © 2016 Malcolm Hall. All rights reserved.
 */
trait Geographical
{

    /**
     * Options variable used to store optional parameters set at runtime
     * @var array
     */
    protected $geographical_options;

    /**
     * Find the distance to all the entries in your table from a particular location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float                                 $latitude  Latitude.
     * @param float                                 $longitude Longitude.
     * @param array|null                            $options   (optional) Array to holds runtime options.
     *      ['table'] string Set a table name to use instead of the default model table (this allows lat/long to be joined to the query from another table).
     *      ['latitude_column'] string Set a column name for latitude at runtime
     *      ['longitude_column'] string Set a column name for longitude at runtime
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDistance(Builder $query, float $latitude, float $longitude, $options = [])
    {
        $this->geographical_options = $options;
        $latName = $this->getQualifiedLatitudeColumn();
        $lonName = $this->getQualifiedLongitudeColumn();

        // Adding already selected columns to query, all columns will be selected by default.
        if ($query->getQuery()->columns === null) {
            $query->select($this->getTable() . '.*');
        } else {
            $query->select($query->getQuery()->columns);
        }

        $sql = "((ACOS(SIN(? * PI() / 180) * SIN(" . $latName . " * PI() / 180) + COS(? * PI() / 180) * COS(" .
            $latName . " * PI() / 180) * COS((? - " . $lonName . ") * PI() / 180)) * 180 / PI()) * 60 * ?) as distance";

        $kilometers = false;
        if (property_exists(static::class, 'kilometers')) {
            $kilometers = static::$kilometers;
        }

        if ($kilometers) {
            $query->selectRaw($sql, [$latitude, $latitude, $longitude, 1.1515 * 1.609344]);
        } else {
            // Miles.
            $query->selectRaw($sql, [$latitude, $latitude, $longitude, 1.1515]);
        }

        return $query;
    }

    /**
     * Find all the entries in your table inside a circular geo-fence.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float                                 $latitude
     * @param float                                 $longitude
     * @param float                                 $inner_radius
     * @param float                                 $outer_radius
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGeofence(
        Builder $query,
        float $latitude,
        float $longitude,
        float $inner_radius,
        float $outer_radius
    ) {
        $query = $this->scopeDistance($query, $latitude, $longitude);
        return $query->havingRaw('distance BETWEEN ? AND ?', [$inner_radius, $outer_radius]);
    }

    protected function getTableName(){
        return isset($this->geographical_options['table']) ?
            $this->geographical_options['table']
            : $this->getTable();
    }

    /**
     * Get full sql column name for latitude.
     *
     * @return string
     */
    protected function getQualifiedLatitudeColumn()
    {
        return $this->getConnection()->getTablePrefix() . $this->getTableName() . '.' . $this->getLatitudeColumn();
    }

    /**
     * Get full sql column name for longitude.
     *
     * @return string
     */
    protected function getQualifiedLongitudeColumn()
    {
        return $this->getConnection()->getTablePrefix() . $this->getTableName() . '.' . $this->getLongitudeColumn();
    }

    /**
     * Get latitude column name
     *
     * @return string
     */
    public function getLatitudeColumn()
    {
        if(isset($this->geographical_options['latitude_column']))
        {
            return $this->geographical_options['latitude_column'];
        }

        if(defined('static::LATITUDE'))
        {
            return static::LATITUDE;
        }

        return 'latitude';
    }

    /**
     * Get longitude column name
     *
     * @return string
     */
    public function getLongitudeColumn()
    {
        if(isset($this->geographical_options['longitude_column']))
        {
            return $this->geographical_options['longitude_column'];
        }

        if(defined('static::LONGITUDE'))
        {
            return static::LONGITUDE;
        }

        return 'longitude';
    }
}
