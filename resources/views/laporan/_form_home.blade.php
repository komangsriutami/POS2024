<div class="row">
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Preview</h3>
        <br>
        <span>Laporan ini menampilan ringkasan seluruh transaksi (Penjualan, Pembelian, Transfer Masuk, dan Transfer Keluar).</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('rekap_all_outlet') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- buku besar -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Transaksi Penjualan</h3>
        <br>
        <span>Laporan ini menampilkan detail transaksi penjualan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('rekap_penjualan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan jurnal -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Transaksi Pembelian</h3>
        <br>
        <span>Laporan ini menampilkan detail transaksi pembelian.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('rekap_pembelian') }}" target="_blank">[ Lihat ]</a>
    </div>

     <!-- laporan laba/rugi -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">HPP</h3>
        <br>
        <span>Laporan ini menampilkan detail harga pokok penjualan (HPP)</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('penjualan/hpp') }}" target="_blank">[ Lihat ]</a>
    </div>

      <!-- laporan laba/rugi -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Omset</h3>
        <br>
        <span>Laporan ini menampilkan detail omset yang dimiliki oulet</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('penjualan/rekap_omset') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan arus kas -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Persediaan</h3>
        <br>
        <span>Laporan ini menampilkan data persediaan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('data_obat/persediaan') }}" target="_blank">[ Lihat ]</a>
    </div>
</div>