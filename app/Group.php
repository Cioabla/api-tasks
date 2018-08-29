<?php
/**
 * Created by PhpStorm.
 * User: andra
 * Date: 29.08.2018
 * Time: 10:29
 */

namespace App;

use Illuminate\Database\Eloquent\Model;


class Group extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'description'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function group()
    {
        return $this->hasMany('App\GroupMembers');
    }
}
