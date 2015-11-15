<?php
/**
 * Created by PhpStorm.
 * User: masoud
 * Date: 11/7/15
 * Time: 3:31 PM
 */

namespace geo\src;


 use Illuminate\Database\Capsule\Manager as Capsule;
 use Illuminate\Events\Dispatcher;
 use Illuminate\Container\Container;
 class db
{
    public static function prepareDatabase()
    {
        $capsule = new Capsule();
        $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'geo',
        'username'  => 'root',
        'password'  => 'iGlL]m1B',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => '']);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

}