<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use App\Traits\DynamicConnectionTrait;

class PrintController extends Controller
{
    use DynamicConnectionTrait;
    public function printToPrinter()
    {
        $content = "Isi teks yang ingin Anda simpan dalam file .txt.";

        $filePath = storage_path() . 'example.txt'; // Ubah 'example.txt' sesuai dengan nama file yang Anda inginkan.

        // Buat file teks
       // file_put_contents($filePath, $content);
        /*
        if (PHP_OS_FAMILY === 'Windows') {
            // Perintah untuk sistem Windows
            exec("notepad /p $filePath");
        } else {
            // Perintah untuk sistem Unix-based (Linux, macOS, dll.)
            exec("lp $filePath");
        }

        return 'File berhasil dicetak.';*/


        //return 'File teks berhasil dibuat dan disimpan di storage dengan nama: ' . $filePath;

        //return view('printtest');
        return view('printtest', ['filePath' => $filePath]);
        exit();
        try {
            $ip = $_SERVER['REMOTE_ADDR'];

            $connector = new WindowsPrintConnector("EPSON TM-U220 Receipt");
            $printer = new Printer($connector);

            // Cetak teks ke printer
            $printer->text("Ini adalah contoh teks yang akan dicetak ke printer.\n");

            // Pemotongan kertas (opsional)
            $printer->cut();

            // Tutup koneksi ke printer
            $printer->close();

            echo "Data berhasil dicetak ke printer.";
        } catch (\Exception $e) {
            return "Gagal mencetak: " . $e->getMessage();
        }
    }
}
