<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'item';
    protected $primaryKey = 'Item_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Item_Id', 'Title', 'details', 'disabled', 'updated_at', 'Class_Id', 'type'
    ];

    
}