<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DynamicConnectionTrait;

class IconThemify extends Model
{
	use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Master Icon Themify
		Author 	: Sri U.
		Date 	: 22/02/2020
	*/
    protected $table = 'm_icon_themify';
    public $primaryKey = 'id';
    protected $fillable = ['icon'];
}
