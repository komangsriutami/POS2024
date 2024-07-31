<div class="row">
    <!-- laporan ringkasan persediaan  barang fifo -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Ringkasan Persediaan Barang FIFO</h3>
        <br>
        <span>Menampilkan daftar kuantitas dan nilai seluruh barang persediaan per tanggal yg ditentukan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/ringkasan_persediaan_barang_fifo') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- buku kuantitas stok gudang -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Kuantitas Stok Gudang</h3>
        <br>
        <span>Laporan ini menampilkan kuantitas stok di setiap gudang untuk semua produk.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/kuantitas_stok_gudang') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan nilai persediaan barang fifo -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Nilai Persediaan Barang FIFO</h3>
        <br>
        <span>Menampilkan pergerakan persediaan barang (kuantitas masuk, keluar, dan nilai harga pokok) dan detil transaksi yang mempengaruhi pergerakan stok untuk semua produk dalam periode yang difilter.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/nilai_persediaan_barang_fifo') }}" target="_blank">[ Lihat ]</a>
    </div>

     <!-- laporan pergerakan barang gudang -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Pergerakan Barang Gudang</h3>
        <br>
        <span>Laporan ini menampilkan pergerakan stok per gudang dan merincikan transaksi yg menghasilkan pergerakan stok per gudang untuk semua produk atau stok per produk untuk semua gudang dalam suatu periode.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/pergerakan_barang_gudang') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan rincian persediaan barang  -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Rincian Persediaan Barang</h3>
        <br>
        <span>Menampilkan daftar transaksi yg terkait dengan setiap Barang dan Jasa, dan menjelaskan bagaimana transaksi tersebut mempengaruhi jumlah stok barang, nilai, dan harga biaya nya.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/rincian_persediaan_barang') }}" target="_blank">[ Lihat ]</a>
    </div>
</div>