<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class JurnalUmumBukti extends Model
{
    use DynamicConnectionTrait;
    protected $table = 'tb_jurnal_umum_bukti';
    public $primaryKey = 'id';
    protected $fillable = ['keterangan', 'file'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_jurnal' => 'required',
            'keterangan' => 'required|max:255',
            'file' => 'required'
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
