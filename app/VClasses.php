<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class VClasses extends Model
{
    protected $table = 'vclasses';
    protected $primaryKey = 'Class_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Class_Id', 'Class', 'Teacher', 'disabled', 'Professor_Id'
    ];

    
}