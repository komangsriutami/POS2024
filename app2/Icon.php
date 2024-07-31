<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class Icon extends Model
{
	use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Master Icon 
		Author 	: 
		Date 	: 
	*/
    protected $table = 'm_icon';
    public $primaryKey = 'id';
    protected $fillable = ['icon'];
}
