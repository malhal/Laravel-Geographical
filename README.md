# Laravel Geographical
Easily add longitude and latitude columns to your records and use inherited functionality for calculating distances.

First either update your database or add this to a migration for each model:

```php
$table->double('longitude');
$table->double('latitude');
```

Finally in your model use:
```php
use Geographical;
```

### 1. Distance

Find the distance to all the entries in your table from a particular location.

```php
$query = Model::distance($latitude, $longitude);
$asc = $query->orderBy('distance', 'ASC')->get();
 ```

### 2. Geofence

Find all the entries in your table inside a circular geo-fence.

```php
$query = Model::geofence($latitude, $longitude, $inner_radius, $outer_radius);
$all = $query->get();
```

### Units

The default unit of distance is **miles**. You can change it to **kilometers** by putting this in your model
```php
protected static $kilometers = true;
```

### Notes

1. The method returns a `Eloquent\Builder` object so that you can add optional conditions if you want.
2. If you require to select only a certain columns, it can be achieved by using `select()`.
    ```php
    Model::select('id', 'name')->distance($latitude, $longitude);
    ```
    (`select()` should precede the `distance()/geofence()`)
3. You can use `distance` as an aggregate column in the result.
(Aggregate columns cannot be used in `WHERE`, use `HAVING` to execute any condition.)
4. If you use different column names for latitude and longitude, mention them in the Model.php
    ```php
    const LATITUDE  = 'lat';
    const LONGITUDE = 'lng';
    ```


## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5+ are required.

To get the latest version of Laravel Geographical, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/laravel-geographical
```
