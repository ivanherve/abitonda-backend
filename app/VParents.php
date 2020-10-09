<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class VParents extends Model
{
    protected $table = 'vparent';
    protected $primaryKey = 'User_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_Id', 'Firstname', 'Surname', 'EmailAddress', 'Profil', 'Profil_Id'
    ];

    
}