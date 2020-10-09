<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'class';
    protected $primaryKey = 'Class_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Class_Id', 'Class', 'disabled', 'Professor_Id'
    ];

    
}