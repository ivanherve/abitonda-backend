<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'student';
    protected $primaryKey = 'Student_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Student_Id', 'Student', 'BirthDate', 'Parent_Id', 'Class_Id', 'disabled'
    ];

    
}