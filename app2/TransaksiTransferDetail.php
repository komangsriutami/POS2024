<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class TransaksiTransferDetail extends Model
{
    use DynamicConnectionTrait;
    // ini tabel detail transfer
    protected $table = 'tb_detail_nota_transfer';
    public $primaryKey = 'id';
    protected $fillable = ['id_nota', 'id_obat', 'jumlah', 'status', 'id_defecta', 'is_titip_order', 'id_satuan', 'keterangan'];

    public function validate(){
    	return Validator::make((array)$this->attributes, [
    		'id_obat' => 'required',
            'id_defecta' => 'required',
        ]);
    }

    public function save_plus(){
        $this->created_by = Auth::user()->id;
        $this->save();
    }

    public function save_edit(){
        $this->updated_by = Auth::user()->id;
        $this->save();
    }

    public function obat(){
        return $this->hasOne('App\MasterObat', 'id', 'id_obat');
    }

    public function satuan(){
        return $this->hasOne('App\MasterSatuan', 'id', 'id_satuan');
    }

    public function nota(){
        return $this->hasOne('App\TransaksiTransfer', 'id', 'id_nota');
    }

    public function TO(){
        return $this->hasOne('App\TransaksiTO', 'id', 'id_nota_transfer_outlet');
    }

    public function detTO(){
        return $this->hasOne('App\TransaksiTODetail', 'id', 'id_det_nota_transfer_outlet');
    }
}
