<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $table = 'messages';
    public $primaryKey = 'id';
    protected $fillable = ['id_user',
    						'message'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'id_user' => 'required|max:255',
            'message' => 'required'
        ]);
    }

    public function user()
    {
      return $this->hasOne('App\User', 'id', 'id_user');
    }

}
