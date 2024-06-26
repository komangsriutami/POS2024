<div class="row">
    <!-- laporan ringkasan aset tetap -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Ringkasan Aset Tetap</h3>
        <br>
        <span>Menampilkan daftar aset tetap yang tercatat, dengan harga awal, akumulasi penyusutan, dan nilai bukunya.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/ringkasan_aset_tetap') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- buku detail aset tetap -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Detail Aset Tetap</h3>
        <br>
        <span>Menampilkan daftar transaksi yg terkait dengan setiap aset, dan menjelaskan bagaimana transaksi tersebut mempengaruhi nilai bukunya.</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/detail_aset_tetap') }}" target="_blank">[ Lihat ]</a>
    </div>

    <!-- laporan sold and dispossal asset -->
    <div class="col-md-6">
        <br>
        <h3 class="card-title text-info">Sold and Dispossal Asset</h3>
        <br>
        <span>Lists of asset that is being sold and/or dispossed</span> 
        <br>
        <a class="btn btn-sm btn-outline-secondary" href="{{ url('laporan/sold_and_dispossal_asset') }}" target="_blank">[ Lihat ]</a>
    </div>
</div>