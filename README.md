# Laravel Geographical
Easily add longitude and latitude columns to your records and use inherited functionality for calculating distances.

First either update your database or add this to a migration for each model:

```php
$table->double('longitude');
$table->double('latitude');
```

Finally, edit you model to use the Geographical Trait, as the example below:
```php
<?php

namespace App\Models;

use Malhal\Geographical\Geographical;
use Illuminate\Database\Eloquent\Model;

class ModelExample extends Model
{
    use Geographical;
```

### 1. Distance

Find the distance to all the entries in your table from a particular location.

```php
$query = ModelExample::distance($latitude, $longitude);
$asc = $query->orderBy('distance', 'ASC')->get();
 ```

### 2. Geofence

Find all the entries in your table inside a circular geo-fence.

```php
$query = ModelExample::geofence($latitude, $longitude, $inner_radius, $outer_radius);
$all = $query->get();
```

> Use `$inner_radius`= 0 & `$outer_radius` = any number in miles that you desire.

### Units

The default unit of distance is **miles**. You can change it to **kilometers** by putting this in your model
```php
protected static $kilometers = true;
```

### Notes

1. The method returns a `Eloquent\Builder` object so that you can add optional conditions if you want.
2. If you require to select only a certain columns, it can be achieved by using `select()`.
    ```php
    ModelExample::select('id', 'name')->distance($latitude, $longitude);
    ```
    (`select()` should precede the `distance()/geofence()`)
3. You can use `distance` as an aggregate column in the result.
(Aggregate columns cannot be used in `WHERE`, use `HAVING` to execute any condition.)
4. If you use different column names for latitude and longitude, mention them in the Model.php
    ```php
    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';
    ```

### Options

1. You may pass an array of options as the third parameter of the distance method, these options will allow you to set a new table name or column names at runtime.
     ```php
    $query = Model::distance($latitude, $longitude, $options);
    ```

2. There are three fields you set with the options parameter at runtime: table, latitude_column, and longitude_column:
    ```php
    $options = [
       'table' => 'coordinates',
       'latitude_column' => 'lat',
       'longitude_column' => 'lon'
    ]
   
    Model::select('id', 'name')->distance($latitude, $longitude, $options);
    ```
3. The table field will allow you to set the table from which the the coordinates will be selected at runtime, allowing you to join the coordinates to your model from another table.
    ```php
   
   Model::join('locations', function($join){ 
               $join->on('model.id', '=', 'locations.model_id');
           })
           ->select('id', 'name')
           ->distance($latitude, $longitude, ['table' => 'locations']);
    ```
4. The latitude_column and longitude_column fields can be used to set the column names for a joined table, or to override the default column names (including those set on your model) at runtime. If you don't set the column name fields at runtime when using a joined table then column names set on your model, or the defaults of 'latitude' and 'longitude' will be used. Setting `const LATITUDE  = 'lat'` or `const LONGITUDE = 'lng'` on a joined model will have no effect.
                                                                                                                                                                                                     
 
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5+ are required.

To get the latest version of Laravel Geographical, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/laravel-geographical
```
