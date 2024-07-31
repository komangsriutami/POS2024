<div class="row">
    <!-- laporan daftar pembelian -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Daftar Pembelian</h3>
        <br>
        <span>Menunjukkan daftar kronologis dari semua pembelian, pemesanan, penawaran, dan pembayaran Anda untuk rentang tanggal yang dipilih.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/daftar_pembelian') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- buku pembelian per suplier -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Pembelian per Supplier</h3>
        <br>
        <span>Menampilkan setiap pembelian dan jumlah untuk setiap Supplier.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/pembelian_per_suplier') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan hutang suplier -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Hutang Suplier</h3>
        <br>
        <span>Menampilkan jumlah nilai yang Anda hutang pada setiap Supplier.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/hutang_suplier') }}" target="_blank">[ Lihat ]</a>
    </div>

     <!-- laporan daftar pengeluaran -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Daftar Pengeluaran</h3>
        <br>
        <span>Daftar seluruh pengeluaran dengan keterangannya untuk kurung waktu yg ditentukan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/daftar_pengeluaran') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan rincian pengeluaran  -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Rincian Pengeluaran</h3>
        <br>
        <span>Laporan ini merincikan pengeluaran-2, dan dikelompokan dalam kategori masing2 dalam jangka waktu yg Anda tentukan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/rincian_pengeluaran') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan usia hutang  -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Usia Hutang</h3>
        <br>
        <span>Laporan ini memberikan ringkasan hutang Anda, menunjukkan setiap vendor Anda secara bulanan, serta jumlah total dari waktu ke waktu. Hal ini praktis untuk membantu melacak hutang Anda.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/usia_hutang') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan pengiriman pembelian -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Pengiriman Pembelian</h3>
        <br>
        <span>Menampilkan semua produk yang dicatat terkirim untuk transaksi pembelian dalam suatu periode, dikelompok per supplier.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/pengiriman_pembelian') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan pembelian per produk -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Pembelian per Produk</h3>
        <br>
        <span>Menampilkan daftar kuantitas pembelian per produk, termasuk jumlah retur, net pembelian, dan harga pembelian rata-rata.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/pembelian_per_produk') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan penyelesaian pemesanan pembelian -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Penyelesaian Pemesanan Pembelian</h3>
        <br>
        <span>Menampilkan ringkasan dari proses bisnis Anda, dari penawaran, pemesanan, pengiriman, penagihan, dan pembayaran per proses, agar Anda dapat melihat penawaran/pemesanan mana yang berlanjut ke penagihan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/penyelesaian_pemesanan_pembelian') }}" target="_blank">[ Lihat ]</a>
    </div>
</div>