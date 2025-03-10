<?php

namespace App\Http\Controllers;

use Flow\ETL\Loader;
use Flow\ETL\Rows;
use Flow\ETL\FlowContext;
use PDO;

class PdoLoader implements Loader
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function load(Rows $rows, FlowContext $context): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO sales (id, id_nota, id_apotek_nota, id_pasien, tgl_nota, id_obat, hbppn, harga_jual, margin, jumlah, diskon, total, total_hbppn) 
             VALUES (:id, :id_nota, :id_apotek_nota, :id_pasien, :tgl_nota, :id_obat, :hbppn, :harga_jual, :margin, :jumlah, :diskon, :total, :total_hbppn)"
        );

        foreach ($rows as $row) {
            $stmt->bindValue(':id', $row->valueOf('id'));
            $stmt->bindValue(':id_nota', $row->valueOf('id_nota'));
            $stmt->bindValue(':id_apotek_nota', $row->valueOf('id_apotek_nota'));
            $stmt->bindValue(':id_pasien', $row->valueOf('id_pasien'));
            $stmt->bindValue(':tgl_nota', $row->valueOf('tgl_nota'));
            $stmt->bindValue(':id_obat', $row->valueOf('id_obat'));
            $stmt->bindValue(':hbppn', $row->valueOf('hbppn'));
            $stmt->bindValue(':harga_jual', $row->valueOf('harga_jual'));
            $stmt->bindValue(':margin', $row->valueOf('margin'));
            $stmt->bindValue(':jumlah', $row->valueOf('jumlah'));
            $stmt->bindValue(':diskon', $row->valueOf('diskon'));
            $stmt->bindValue(':total', $row->valueOf('total'));
            $stmt->bindValue(':total_hbppn', $row->valueOf('total_hbppn'));
            $stmt->execute();
        }
    }
}
