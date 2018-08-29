<?php
/**
 * Created by PhpStorm.
 * User: andra
 * Date: 29.08.2018
 * Time: 10:39
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class GroupMembers extends Model
{
    protected $table = 'groupsMembers';

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public function group_find()
    {
        return $this->belongsTo('App\Group','group_id','id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}