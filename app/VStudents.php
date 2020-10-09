<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class VStudents extends Model
{
    protected $table = 'vstudents';
    protected $primaryKey = 'Student_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Student_Id', 'Student', 'BirthDate', 'Parent', 'Class', 'PhotoId'
    ];

    
}