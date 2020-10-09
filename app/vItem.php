<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class VItem extends Model
{
    protected $table = 'vitem';
    protected $primaryKey = 'Item_Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Item_Id', 'PdfItem_Id', 'LInkItem_Id', 'Title', 'File', 'Link', 'details', 'disabled', 'updated_at', 'Class_Id'
    ];
}
