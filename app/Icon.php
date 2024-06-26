<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Icon extends Model
{
    /* 
		Model 	: Untuk Master Icon 
		Author 	: 
		Date 	: 
	*/
    protected $table = 'm_icon';
    public $primaryKey = 'id';
    protected $fillable = ['icon'];
}
