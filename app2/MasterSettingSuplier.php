<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
use App\Traits\DynamicConnectionTrait;

class MasterSettingSuplier extends Model
{
    use DynamicConnectionTrait;
    use HasFactory;

    /*
        Model   : Model spesialis untuk Dokter@Controller
        Author  : Tangkas.
        Date    : 16/06/2021
    */

    protected $table = 'tb_m_setting_suplier';
    public $primaryKey = 'id';
    protected $fillable = [
        'id_obat',
        'id_suplier',
        'level'
    ];

    public function validate(){
        return Validator::make((array)$this->attributes, [
            'id_obat' => 'required',
            'id_suplier' => 'required',
            'level' => 'required',
        ]);
    }

    public function suplier()
    {
        return $this->hasOne('App\MasterSuplier', 'id', 'id_suplier');
    }

    public function obat()
    {
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }
}
