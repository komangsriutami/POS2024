<?php

namespace App\DataTables;

use App\SO\StokHargaLV;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use App\MasterApotek;
use App\SettingStokOpnam;
use DB;

class StokLVDataTable extends DataTable
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
                ->addColumn('action', function($query){
                    $btn = '<div class="btn-group">';
                        $btn .= '<span class="btn btn-default" onClick="reload_stok_awal('.$query->id.')" data-toggle="tooltip" data-placement="top" title="Reload Stok Awal"><i class="fa fa-retweet"></i></span>';
                    $btn .='</div>';
                    return $btn;
                });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\StokHargaLV $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(StokHargaLV $model)
    {
        $so_status_aktif = session('so_status_aktif');
        $inisial = session('nama_apotek_singkat_active');
        return $model->newQuery()
                    ->select(DB::raw('IFNULL(so_by_nama, null) as so_by'), 'tb_m_stok_harga_'.$inisial.'.id', 'tb_m_stok_harga_'.$inisial.'.id_obat', 'tb_m_stok_harga_'.$inisial.'.stok_awal', 'tb_m_stok_harga_'.$inisial.'.stok_akhir_so', 'tb_m_stok_harga_'.$inisial.'.harga_beli', 'tb_m_stok_harga_'.$inisial.'.harga_jual', 'tb_m_stok_harga_'.$inisial.'.nama', 'tb_m_stok_harga_'.$inisial.'.barcode', 'tb_m_stok_harga_'.$inisial.'.is_so', 'tb_m_stok_harga_'.$inisial.'.stok_awal_so', 'tb_m_stok_harga_'.$inisial.'.selisih', 'tb_m_stok_harga_'.$inisial.'.total_penjualan_so')
                    ->where(function($query) use($so_status_aktif, $inisial){
                        if($so_status_aktif == 2) {
                            $query->where('tb_m_stok_harga_'.$inisial.'.selisih','!=','0');
                        } else if($so_status_aktif == 3) {
                            $query->where('tb_m_stok_harga_'.$inisial.'.is_so', 1);
                            $query->where('tb_m_stok_harga_'.$inisial.'.stok_awal_so', '!=','0');
                            $query->where('tb_m_stok_harga_'.$inisial.'.stok_akhir_so','0');
                        } else if($so_status_aktif == 4) {
                            $query->where('tb_m_stok_harga_'.$inisial.'.is_so', 0);
                            $query->where('tb_m_stok_harga_'.$inisial.'.stok_awal_so', '!=','0');
                        }
                    });
                    //->orderBy('tb_m_stok_harga_'.$inisial.'.id', 'asc');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $inisial = session('nama_apotek_singkat_active');
        return $this->builder()
                    ->setTableId('tb_m_stok_harga_'.$inisial.'-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                   /* ->orderBy(1, 'asc')*/
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
        $inisial = session('nama_apotek_singkat_active');
        return [

            Column::make('id'),
            [
                'data' => 'barcode',
                'editField' => 'barcode',
                'name' => 'tb_m_stok_harga_'.$inisial.'.barcode',
                'title' => 'Barcode',
                'orderable' => true,
                'searchable' => true,
                //'className' => 'editable'
            ],
            [
                'data' => 'nama',
                'editField' => 'nama',
                'name' => 'tb_m_stok_harga_'.$inisial.'.nama',
                'title' => 'Barcode',
                'orderable' => true,
                'searchable' => true,
                //'className' => 'editable'
            ],
            [
                'data' => 'harga_beli',
                'editField' => 'harga_beli',
                'name' => 'tb_m_stok_harga_'.$inisial.'.harga_beli',
                'title' => 'Harga Beli',
                'orderable' => true,
                //'searchable' => true,
                //'className' => 'editable'
            ],
            [
                'data' => 'harga_jual',
                'editField' => 'harga_jual',
                'name' => 'tb_m_stok_harga_'.$inisial.'.harga_jual',
                'title' => 'Harga Jual',
                'orderable' => true,
               // 'searchable' => true,
                //'className' => 'editable'
            ],
            //Column::make('username'),
            [
                'data' => 'so_by',
                'title' => 'Oleh',
                'orderable' => true,
                //'searchable' => true
            ],
            [
                'data' => 'selisih',
                'title' => 'Selisih',
                'orderable' => true,
                //'searchable' => true
            ],
            [
                'data' => 'stok_awal_so',
                'title' => 'Stok Awal',
                'orderable' => true,
                //'searchable' => true
            ],
            [
                'data' => 'total_penjualan_so',
                'title' => 'Penjualan',
                'orderable' => true,
                //'searchable' => true
            ],
            [
                'data' => 'stok_akhir_so',
                'editField' => 'stok_akhir_so',
                'name' => 'stok_akhir_so',
                'title' => 'Stok Akhir',
                'orderable' => true,
                //'searchable' => true,
                'className' => 'editable'
            ],
            [
                'data' => 'action',
                'name' => 'action',
                'title' => 'Action',
                'orderable' => false,
                //'searchable' => true,
                'className' => 'editable'
            ],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        $inisial = session('nama_apotek_singkat_active');
        return 'tb_m_stok_harga_'.$inisial.'_' . time();
    }
}
