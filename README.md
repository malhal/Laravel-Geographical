# Laravel-Geographical
Easily add longitude and latitude columns to your records and use inherited functionality for calculating distances.

First either update your database or add this to a migration for each model:

    $table->double('longitude');
    $table->double('latitude');

Finally in your model use:

    use Geographical;

Now to query by distance use this:

    $query->getModel()->newDistanceQuery($request->query('lat'), $request->query('lon'))->orderBy('miles', 'asc')->get();
    
## Installation

[PHP](https://php.net) 5.6.4+ and [Laravel](http://laravel.com) 5.3+ are required.

To get the latest version of Laravel CreatedBy, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require malhal/laravel-geographical
```
