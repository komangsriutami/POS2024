<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use App\DataTables\ModelCustome;
use Auth;
use DB;
use App\MasterObat;
use App\SO\StokHargaCustome;
class StokSODataTableEditor extends DataTablesEditor
{
    protected $model = StokHargaCustome::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        /*return [
            'id_obat'  => 'required',
            'stok_akhir_so'  => 'required',
        ];*/
        return [];
    }

    /**
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function editRules(Model $model)
    {
        /*return [
            'id_obat'  => 'sometimes|required',
            'stok_akhir_so'  => 'sometimes|required',
        ];*/
        return [];
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
        $inisial = session('nama_apotek_singkat_active');
        $id_so = session('id_so');
        if(isset($data['stok_akhir_so'])) {
            #echo "update stok";
            #total = total_penjualan - total_hapus - total_retur + total_batal_retur | NOTED : tidak boleh selain transaksi penjualan murni
            $total_penjualan = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $model->id_obat)->where('id_jenis_transaksi', 1)->whereDate('created_at', date('Y-m-d'))->first();
            $total_hapus = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $model->id_obat)->where('id_jenis_transaksi', 15)->whereDate('created_at', date('Y-m-d'))->first();
            $total_retur = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $model->id_obat)->where('id_jenis_transaksi', 5)->whereDate('created_at', date('Y-m-d'))->first();
            $total_batal_retur = DB::table('tb_histori_stok_'.$inisial)->select([DB::raw('SUM(jumlah) as total')])->where('id_obat', $model->id_obat)->where('id_jenis_transaksi', 6)->whereDate('created_at', date('Y-m-d'))->first();
            $count_penjualan = ($total_penjualan->total+$total_batal_retur->total)-($total_hapus->total+$total_retur->total);
            $stok_awal_so = $model->stok_awal_so;
            $stok_awal = $stok_awal_so-$count_penjualan;
            $stok_akhir = $data['stok_akhir_so'];
            $stok_akhir_so = $data['stok_akhir_so'];
            $selisih = ($stok_akhir_so+$count_penjualan)-$stok_awal_so;

            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->first();
            DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->update(['stok_awal'=> $stok_awal, 'stok_akhir' => $stok_akhir, 'stok_akhir_so' => $stok_akhir_so, 'total_penjualan_so' => $count_penjualan, 'selisih' => $selisih, 'id_so' => $id_so, 'is_so' => 1, 'so_at' => date('Y-m-d H:i:s'), 'so_by' => Auth::user()->id, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id, 'so_by_nama' => Auth::user()->username]);

            # create histori
            DB::table('tb_histori_stok_'.$inisial)->insert([
                'id_obat' => $model->id_obat,
                'jumlah' => $stok_akhir_so,
                'stok_awal' => $stok_awal_so,
                'stok_akhir' => $stok_akhir_so,
                'id_jenis_transaksi' => 11, //stok opnam
                'id_transaksi' => $id_so,
                'batch' => null,
                'ed' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);   
        } else if(isset($data['nama'])) {
            # echo "update nama";
            $obat = MasterObat::find($model->id_obat);
            $obat->nama = $data['nama'];
            $obat->updated_at = date('Y-m-d H:i:s');
            $obat->updated_by = Auth::user()->id;
            $obat->save();
        } else if(isset($data['barcode'])) {
            # echo "update barcode";
            $obat = MasterObat::find($model->id_obat);
            $obat->barcode = $data['barcode'];
            $obat->updated_at = date('Y-m-d H:i:s');
            $obat->updated_by = Auth::user()->id;
            $obat->save();
        } else if(isset($data['harga_jual'])) {
            #echo "update harga jual";
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->first();
            DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->update(['harga_jual'=> $data['harga_jual'], 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id, 'so_by_nama' => Auth::user()->username]);

            # create histori
            DB::table('tb_histori_harga_'.$inisial)->insert([
                'id_obat' => $model->id_obat,
                'harga_beli_awal' => $stok_before->harga_beli,
                'harga_beli_akhir' => $stok_before->harga_beli,
                'harga_jual_awal' => $stok_before->harga_jual,
                'harga_jual_akhir' => $data['harga_jual'], //stok opnam
                'is_asal' => 2,
                'id_asal' => $id_so,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);   
        } else if(isset($data['harga_beli'])) {
            # echo "update harga beli";
            $stok_before = DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->first();
            DB::table('tb_m_stok_harga_'.$inisial)->where('id', $model->id)->update(['harga_beli'=> $data['harga_beli'], 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => Auth::user()->id, 'so_by_nama' => Auth::user()->username]);

            # create histori
            DB::table('tb_histori_harga_'.$inisial)->insert([
                'id_obat' => $model->id_obat,
                'harga_beli_awal' => $stok_before->harga_beli,
                'harga_beli_akhir' => $data['harga_beli'],
                'harga_jual_awal' => $stok_before->harga_jual,
                'harga_jual_akhir' => $stok_before->harga_jual, //stok opnam
                'is_asal' => 2,
                'id_asal' => $id_so,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]);  
        }
        
        return $data;
    }
}
