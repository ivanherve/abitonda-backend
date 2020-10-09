<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class LinkItem extends Model
{
    protected $table = 'vlinkitem';
    protected $primaryKey = 'LInkItem_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'LInkItem_Id', 'Title', 'Link', 'details', 'disabled', 'updated_at', 'Class_Id'
    ];

    
}