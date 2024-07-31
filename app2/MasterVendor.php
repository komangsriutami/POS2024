<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use DB;
use App\Traits\DynamicConnectionTrait;

class MasterVendor extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_vendor_kerjasama';
    public $primaryKey = 'id';
    protected $fillable = ['nama',
                            'diskon'
    						];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
            'nama' => 'required',
        ]);
    }
}
