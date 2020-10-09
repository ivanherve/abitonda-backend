<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    protected $table = 'profiluser';
    protected $primaryKey = 'Profil_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Profil_Id', 'Profil'
    ];

    
}