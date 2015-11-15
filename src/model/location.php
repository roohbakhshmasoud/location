<?php
/**
 * Created by PhpStorm.
 * User: masoud
 * Date: 11/1/15
 * Time: 10:20 AM
 */

namespace geo\src\model;

use Illuminate\Database\Eloquent\Model;

class location extends Model
{
    protected $fillable = array('type', 'parent','polygon','region_id','detail');

    protected $table = 'geoLocation';
    public $timestamps = false;



}