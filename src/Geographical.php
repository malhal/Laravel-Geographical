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
     * Find the distance to all the entries in your table from a particular location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float                                 $latitude  Latitude.
     * @param float                                 $longitude Longitude.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDistance(Builder $query, float $latitude, float $longitude)
    {
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

    /**
     * Get full sql column name for latitude.
     *
     * @return string
     */
    protected function getQualifiedLatitudeColumn()
    {
        return $this->getConnection()->getTablePrefix() . $this->getTable() . '.' . $this->getLatitudeColumn();
    }

    /**
     * Get full sql column name for longitude.
     *
     * @return string
     */
    protected function getQualifiedLongitudeColumn()
    {
        return $this->getConnection()->getTablePrefix() . $this->getTable() . '.' . $this->getLongitudeColumn();
    }

    /**
     * Get latitude column name
     *
     * @return string
     */
    public function getLatitudeColumn()
    {
        return defined('static::LATITUDE') ? static::LATITUDE : 'latitude';
    }

    /**
     * Get longitude column name
     *
     * @return string
     */
    public function getLongitudeColumn()
    {
        return defined('static::LONGITUDE') ? static::LONGITUDE : 'longitude';
    }
}
