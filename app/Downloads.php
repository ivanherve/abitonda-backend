<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Downloads extends Model
{
    protected $table = 'downloads';
    protected $primaryKey = ['User_Id', 'Item_Id'];
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_Id', 'Item_Id', 'nbDownloads'
    ];

    
}