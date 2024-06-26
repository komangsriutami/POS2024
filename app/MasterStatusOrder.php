<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterStatusOrder extends Model
{
    /* 
		Model 	: Untuk Master Status Order
		Author 	: Sri U.
		Date 	: 26/02/2020
	*/
		
	protected $table = 'tb_m_status_order';
    public $primaryKey = 'id';
    protected $fillable = ['id','nama'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required|max:255',
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
}
