<?php
/**
 * Created by PhpStorm.
 * User: masoud
 * Date: 11/7/15
 * Time: 3:42 PM
 */

namespace geo\src\model;


use Illuminate\Database\Eloquent\Model;

class meta extends Model
{
    protected $fillable = array('name', 'attitude','lattitude');

    protected $table = 'metadata';
    public $timestamps = false;

    public function location()
    {
        return $this->belongsTo('geo\src\model\location' , 'id');
    }
}