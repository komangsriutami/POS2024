<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Traits\DynamicConnectionTrait;

class MessageFooter extends Model
{
    use DynamicConnectionTrait;
    /*
		Model 	: Untuk Message
		Author 	: Tangkas.
		Date 	: 31/07/2021
	*/
    protected $table = 'tb_message';
    public $primaryKey = 'id';
    protected $fillable = ['name','email', 'phone_number', 'additional_message'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|max:20',
            'additional_message' => 'required|max:255',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function created_oleh(){
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh(){
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
