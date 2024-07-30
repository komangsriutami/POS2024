<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;

use App\MasterKodeAkun;

class BiayaTemplateExport implements WithHeadings, WithColumnWidths, WithTitle, FromCollection
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
            return [
                    'Kode Akun Bayar Dari',
                    'Bayar Nanti(y/n)', 
                    'Batas Pembayaran', 
                    '(*)No. Transaksi', 
                    '(*)Tanggal Transaksi', 
                    'Kode Cara Pembayaran (1:cash,2:transfer)', 
                    '(*)ID Supplier',
                    '(*)Alamat Penagihan', 
                    'tag (pisahkan dengan tanda ,)', 
                    'Memo', 
                    'Kode Akun Pajak Potong', 
                    'Nominal Potongan Pajak', 
                    '(*)Kode Akun', 
                    '(*)Deskripsi', 
                    'Kode Akun Pajak (pisahkan dengan tanda ,)',
                    '(*)Biaya (tanpa titik)'
                ];
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
                'B' => 20,
                'C' => 20,
                'D' => 30,
                'E' => 30,
                'F' => 30,
                'G' => 20,        
                'H' => 20,        
                'I' => 20,        
                'J' => 20,        
                'K' => 20,        
                'L' => 20,        
                'M' => 20,        
                'N' => 20,        
                'O' => 20,        
                'P' => 20        
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
            
            $data = MasterKodeAkun::on($this->getConnectionName())->whereNull('deleted_by')->get();
            if($data->count()){
                foreach ($data as $key => $value) {
                    $collection[] = array(($key+1),$value->kode,$value->nama);
                }
            } else {
                $collection[] = array('','','');
            }

        } else {
            $collection[] = array();
        }

        return $collection;
    }

}