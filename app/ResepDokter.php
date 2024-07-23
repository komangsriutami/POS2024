<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\DynamicConnectionTrait;

class ResepDokter extends Model
{
    use DynamicConnectionTrait;
    /*
		Model 	: Model Resep Dokter untuk ResepDokter@Controller
		Author 	: Tangkas.
		Date 	: 16/06/2021
	*/

    protected $table = 'tb_m_resep_dokter';
    public $primaryKey = 'id';
    protected $fillable = [
        'resep_dokter',
        'keluhan_pasien',
        'diagnosa_pasien',
        'id_tindakan',
        'tindakan_tambahan',
        'fee',
        'hasil_lab'
    ];

    public function validate()
    {
        return Validator::make((array)$this->attributes, [
            'resep_dokter' => 'required',
            'keluhan_pasien' => 'required',
            'diagnosa_pasien' => 'required',
            'id_tindakan' => 'required',
            'tindakan_tambahan' => 'required',
            'fee' => 'required',
            'hasil_lab' => 'required'
        ]);
    }

    //tambahan tangkas 16/06/2021
    public function jenis_tindakans()
    {
        return $this->hasOne('App\MasterTindakan', 'id', 'id_tindakan');
    }
    //tangkas 16/06/2021

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
