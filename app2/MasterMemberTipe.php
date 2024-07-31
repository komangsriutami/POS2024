<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class MasterMemberTipe extends Model
{
    use DynamicConnectionTrait;
    /* 
		Model 	: Untuk Master Tipe Member 
		Author 	: Sri U.
		Date 	: 27/02/2020
	*/
		
	protected $table = 'tb_m_tipe_member';
    public $primaryKey = 'id';
    protected $fillable = ['nama', 
                            'etichal',
                            'non_etichal', 
                            'limit'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'etichal' => 'required',
            'non_etichal' => 'required',
            'limit' => 'required'
        ]);
    }
}
