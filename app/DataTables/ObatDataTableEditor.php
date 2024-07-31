<?php

namespace App\DataTables;

use App\MasterObat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Auth;
use DB;
class ObatDataTableEditor extends DataTablesEditor
{
    protected $model = MasterObat::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'id'  => 'required',
            'id_golongan_obat'  => 'required',
        ];
    }

    /**
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            //'email' => 'sometimes|required|email|' . Rule::unique($model->getTable())->ignore($model->getKey()),
            'id'  => 'sometimes|required',
            'id_golongan_obat'  => 'sometimes|required',
        ];
    }

    /**
     * Get remove action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function removeRules(Model $model)
    {
        return [];
    }

    /**
    * Pre-create action event hook.
    *
    * @param Model $model
    * @return array
    */
    public function creating(Model $model, array $data)
    {
        return $data;
    }

    /**
    * Pre-update action event hook.
    *
    * @param Model $model
    * @return array
    */
    public function updating(Model $model, array $data)
    {
        if(isset($data['id_golongan_obat'])) {
            #echo "update stok";
            DB::table('tb_m_obat')->where('id', $model->id)->update(['id_golongan_obat'=> $data['id_golongan_obat'], 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id]);

        }

        return $data;
    }
}
