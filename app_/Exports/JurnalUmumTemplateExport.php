<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;

use App\MasterKodeAkun;

class JurnalUmumTemplateExport implements WithHeadings, WithColumnWidths, WithTitle, FromCollection
{
    protected $jenis;

    public function __construct(int $jenis)
    {
       $this->jenis = $jenis;
    }

    public function headings(): array
    {
        if($this->jenis == 1){
            return ['No.','Kode Akun','Nama Akun'];
        } else {
            return ['No. Transaksi(*)', 'Tanggal Transaksi(*)', 'Kode Referensi / Kontak', 'tag', 'Memo', 'Kode Akun(*)', 'Deskripsi(*)', 'Kredit (tanpa titik)', 'Debit (tanpa titik)'];
        }
    }

    public function columnWidths(): array
    {
        if($this->jenis == 1){
            return [
                'A' => 15,
                'B' => 15,
                'C' => 50
            ];
        } else {
            return [
                'A' => 15,
                'B' => 15,
                'C' => 20,
                'D' => 20,
                'E' => 20,
                'F' => 30,
                'G' => 30,
                'H' => 20,
                'I' => 20        
            ];
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        if($this->jenis == 1){
            return 'Glossarium';
        } else {
            return 'Template Import';
        }
    }



    /**
     * @return Builder
     */
    public function collection()
    {
        $collection = collect();

        if($this->jenis == 1){
            
            $data = MasterKodeAkun::whereNull('deleted_by')->get();
            if($data->count()){
                foreach ($data as $key => $value) {
                    $collection[] = array(($key+1),$value->kode,$value->nama);
                }
            } else {
                $collection[] = array('','','');
            }

        } else {
            $collection[] = array('','','','','','','','','');
        }

        return $collection;
    }

}