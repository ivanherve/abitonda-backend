<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'token';
    protected $primaryKey = 'Token_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'User_Id', 'api_token', 'Token_id'
    ];

    public function user() {
        $this->belongsTo(User::class, 'User_id');
    }
}