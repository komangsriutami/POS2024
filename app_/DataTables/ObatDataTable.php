<?php

namespace App\DataTables;

use App\MasterObat;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\MasterApotek;
use DB;
class ObatDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables($query)->setRowId('id')
                ->addColumn('nama', function($query){
                    return $query->nama; 
                })
                ->addColumn('barcode', function($query){
                    return $query->barcode; 
                })
                ->addColumn('updated_at', function($query){
                    return $query->updated_at; 
                })
                 ->addColumn('jenis_obat', function($query){
                    if($query->id_golongan_obat == 1) {
                        $string = "Etichal";
                    } else  if($query->id_golongan_obat == 2) {
                        $string = "Non etichal";
                    }
                    else {
                        $string = "Belum Dikonfirmasi";
                    }
                    return $string; 
                });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\MasterObat $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MasterObat $model)
    {
        return $model->newQuery()
                    ->select(DB::raw('IFNULL(users.username, null) as updated_by'), 'tb_m_obat.id', 'tb_m_obat.nama', 'tb_m_obat.barcode', 'tb_m_obat.id_golongan_obat', 'tb_m_obat.updated_at')
                    ->leftJoin( 'users', 'users.id', '=', 'tb_m_obat.updated_by' )
                    ->where(function($query) {
                        $query->where('tb_m_obat.is_deleted','=','0');
                    })
                    ->orderBy('tb_m_obat.id', 'asc');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('tb_m_obat-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1, 'asc')
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [

            Column::make('id'),
            [
                'data' => 'barcode',
                'editField' => 'barcode',
                'name' => 'tb_m_obat.barcode',
                'title' => 'Barcode',
                'orderable' => true,
                'searchable' => true,
                //'className' => 'editable'
            ],
            [
                'data' => 'nama',
                'editField' => 'nama',
                'name' => 'tb_m_obat.nama',
                'title' => 'Barcode',
                'orderable' => true,
                'searchable' => true,
                //'className' => 'editable'
            ],
            [
                'data' => 'id_golongan_obat',
                'editField' => 'id_golongan_obat',
                'name' => 'tb_m_obat.id_golongan_obat',
                'title' => 'ID Jenis Obat',
               // 'type' => 'select',
                'orderable' => true,
                //'searchable' => true,
                'className' => 'editable'
                
            ],
            [
                'data' => 'jenis_obat',
                'editField' => 'jenis_obat',
                'name' => 'jenis_obat',
                'title' => 'Jenis Obat',
                'orderable' => true,
                //'searchable' => true,
               // 'className' => 'editable'
                
            ],
            //Column::make('username'),
            [
                'data' => 'updated_by',
                'title' => 'Oleh',
                'orderable' => true,
                //'searchable' => true
            ],
            [
                'data' => 'updated_at',
                'title' => 'Last Update',
                'orderable' => true,
                //'searchable' => true
            ]
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'tb_m_obat_' . time();
    }
}
