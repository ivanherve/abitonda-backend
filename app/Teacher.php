<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'professor';
    protected $primaryKey = 'Professor_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Professor_Id', 'User_Id'
    ];
}
