<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\DynamicConnectionTrait;

class MasterTindakan extends Model
{
    use DynamicConnectionTrait;
    /*
		Model 	: Model Master Tindakan untuk Tindakan@Controller
		Author 	: Tangkas.
		Date 	: 16/06/2021
	*/

    protected $table = 'tb_m_tindakan';
    public $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'harga',
        'keterangan'
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'nama' => 'required',
            'keterangan' => 'required'
        ]);
    }

    public function save_plus()
    {
        $this->created_by = Auth::user()->id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function save_edit()
    {
        $this->updated_by = Auth::user()->id;
        $this->updated_at = date('Y-m-d H:i:s');
        $this->save();
    }

    public function created_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'created_by');
    }

    public function updated_oleh()
    {
        return $this->hasOne('App\Users', 'id', 'updated_by');
    }
}
