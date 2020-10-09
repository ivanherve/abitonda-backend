<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class BlobItem extends Model
{
    protected $table = 'vblobitem';
    protected $primaryKey = 'PdfItem_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'PdfItem_Id', 'Title', 'File', 'details', 'disabled', 'updated_at', 'Class_Id'
    ];

    
}