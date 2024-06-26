<div class="row">
    <!-- laporan daftar penjualan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Daftar Penjualan</h3>
        <br>
        <span>Menunjukkan daftar kronologis dari semua faktur, pemesanan, penawaran, dan pembayaran Anda untuk rentang tanggal yang dipilih.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/daftar_penjualan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- buku penjualan per pelanggan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Penjualan per Pelanggan</h3>
        <br>
        <span>Menampilkan setiap transaksi penjualan untuk setiap pelanggan, termasuk tanggal, tipe, jumlah dan total.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/penjualan_per_pelanggan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan piutang pelanggan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Piutang Pelanggan</h3>
        <br>
        <span>Menampilkan tagihan yang belum dibayar untuk setiap pelanggan, termasuk nomor & tanggal faktur, tanggal jatuh tempo, jumlah nilai, dan sisa tagihan yang terhutang pada Anda.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/piutang_pelanggan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan usia piutang  -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Usia Piutang</h3>
        <br>
        <span>Laporan ini memberikan ringkasan piutang Anda, yang menunjukkan setiap pelanggan karena Anda secara bulanan, serta jumlah total dari waktu ke waktu. Hal ini praktis untuk membantu melacak piutang Anda.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/usia_piutang') }}" target="_blank">[ Lihat ]</a>
    </div>

     <!-- laporan pengiriman penjualan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Pengiriman Penjualan</h3>
        <br>
        <span>Menampilkan semua produk yang dicatat terkirim untuk transaksi penjualan dalam suatu periode, dikelompok per pelanggan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/pengiriman_penjualan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan pengiriman penjualan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Penjualan per Produk</h3>
        <br>
        <span>Menampilkan daftar kuantitas penjualan per produk, termasuk jumlah retur, net penjualan, dan harga penjualan rata-rata.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/penjulan_per_produk') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan penyelesaian pemesanan penjualan -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Penyelesaian Pemesanan Penjualan</h3>
        <br>
        <span>Menampilkan ringkasan dari proses bisnis Anda, dari penawaran, pemesanan, pengiriman, penagihan, dan pembayaran per proses, agar Anda dapat melihat penawaran/pemesanan mana yang berlanjut ke penagihan.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/penyelesaian_pemesanan_penjualan') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan profitabilitas produk -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Profitabilitas Produk</h3>
        <br>
        <span>Melihat keuntungan total yang diperoleh dari produk tertentu.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/profitabilitas_produk') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan anggaran laba/rugi -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Daftar Proforma Invoice</h3>
        <br>
        <span>Menunjukkan daftar kronologis dari proforma invoice dan pembayaran Anda untuk rentang tanggal yang dipilih.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/daftar_performa_invoice') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan daftar tukar faktur -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Daftar Tukar Faktur</h3>
        <br>
        <span>Menunjukkan daftar kronologis dari transaksi tukar faktur dan pembayaran Anda untuk rentang tanggal yang dipilih.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/daftar_tukar_faktur') }}" target="_blank">[ Lihat ]</a>
    </div>
</div>