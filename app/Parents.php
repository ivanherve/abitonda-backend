<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    protected $table = 'parent';
    protected $primaryKey = 'Parent_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_Id', 'Parent_Id'
    ];

    
}