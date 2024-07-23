<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use App\User;
use App\MasterObat;
use App\TransaksiTO;
use App\TransaksiTODetail;
use Illuminate\Support\Collection;
use Auth;
class TOImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $i = 0;
        $array = array();
        foreach ($rows as $row) 
        {
            $cek = MasterObat::on($this->getConnectionName())->where('nama', $row[0])->first();
            if(!empty($cek)) {
                $detail = TransaksiTODetail::on($this->getConnectionName())->where('id_nota', 12909)->where('id_obat', $cek->id)->first();
                if(!empty($detail)) {
                     TransaksiTODetail::on($this->getConnectionName())->where('id_nota', 12909)->where('id_obat', $cek->id)
                    ->update([
                        'jumlah' => $row[1], 
                        'harga_outlet' => $row[3]/$row[1], 
                        'total' => $row[3],
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => Auth::user()->id
                    ]);

                    /*$detail->jumlah = $row[1];
                    $detail->harga_outlet = $row[2];
                    $detail->total = $row[3];
                    $detail->updated_at = date('Y-m-d H:i:s');
                    $detail->updated_by = Auth::user()->id;
                    $detail->save();*/
                    $i++;
                } else {
                    $detail = new TransaksiTODetail;
                    $detail->setDynamicConnection();
                    $detail->id_nota = 12909;
                    $detail->id_obat = $cek->id;
                    $detail->jumlah = $row[1];
                    $detail->harga_outlet = $row[2];
                    $detail->total = $row[3];
                    $detail->created_at = date('Y-m-d H:i:s');
                    $detail->created_by = Auth::user()->id;
                    $detail->save();
                    $i++;
                }
            } else {
                $array[] = $row[0];
            }
        }
    }
}
