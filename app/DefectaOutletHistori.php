<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;
use Auth;
use App\Traits\DynamicConnectionTrait;

class DefectaOutletHistori extends Model
{
    use HasFactory;
    use DynamicConnectionTrait;
    protected $table = 'tb_defecta_outlet_histori';
    public $primaryKey = 'id';
    protected $fillable = ['id_defecta', 'id_status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'id_process'];
}
