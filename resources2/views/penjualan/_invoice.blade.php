@extends('layout.app_penjualan')

@section('title')
Transaksi Penjualan
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
    <li class="breadcrumb-item"><a href="#">Transaksi Penjualan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Invoice</li>
</ol>
@endsection

@section('content')
	<div class="row">
	    <div class="col-12">
	        <div class="callout callout-info">
	            <h5><i class="fas fa-info"></i> Note:</h5>
	            Halaman ini telah dilengkapi dengan fitur cetak. Klik tombol print untuk mencetak invoice ini.
	        </div>
	        <!-- Main content -->
	        <div class="invoice p-3 mb-3">
	            <!-- title row -->
	            <div class="row">
	                <div class="col-12">
	                    <h4>
	                        <!-- <i class="fas fa-globe"></i>  -->APOTEKEREN
	                        <small class="float-right">Date: {{ $date_now }}</small>
	                    </h4>
	                </div>
	                <!-- /.col -->
	            </div>
	            <!-- info row -->
	            <div class="row invoice-info">
	                <div class="col-sm-4 invoice-col">
	                    From
	                    <address>
	                        <strong>Apotek {{ $apotek->group_apotek->nama_singkat }} {{ $apotek->nama_panjang }}</strong><br>
	                        {{ $apotek->alamat }}<br>
	                        Phone: {{ $apotek->telepon }}<br>
	                        Email: {{ $apotek->email }}
	                    </address>
	                </div>
	                <!-- /.col -->
	                <div class="col-sm-4 invoice-col">
	                    To
	                    <strong>{{ $vendor->nama }}</strong><br>
                        {{ $vendor->alamat }}<br>
                        Phone: {{ $vendor->telepon }}<br>
                        Email: -
	                </div>
	                <!-- /.col -->
	                <div class="col-sm-4 invoice-col">
	                    <b>Invoice #{{ $penjualan->id }}</b><br>
	                    <br>
	                    <b>Payment Due:</b> 2/22/2014<br>
	                    <b>Account:</b> 968-34567<br>
	                    <b>Account Name:</b> BCA 
	                </div>
	                <!-- /.col -->
	            </div>
	            <!-- /.row -->
	            <!-- Table row -->
	            <?php $total = 0; ?>
	            <div class="row">
	                <div class="col-12 table-responsive">
	                    <table class="table table-striped">
	                        <thead>
	                            <tr>
	                                <th width="2%">No</th>
	                                <th width="50%">Nama</th>
	                                <th width="8%" class="text-right">Jumlah</th>
	                                <th width="15%" class="text-right">Harga</th>
	                                <th width="10%" class="text-right">Diskon</th>
	                                <th width="15%" class="text-right">Subtotal</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        	<?php $no = 0; ?>
	                        	@foreach($detail_penjualans as $obj)
	                        	<?php 
	                        		$no++; 
	                        		$harga_jual = 'Rp '.number_format($obj->harga_jual, 2);
	                        		$diskon = 'Rp '.number_format($obj->diskon, 2);
	                        		$subtotal = ($obj->jumlah*$obj->harga_jual)-$obj->diskon;
	                        		$total = $total + $subtotal;
	                        		$subtotal_f = 'Rp '.number_format($subtotal, 2);
	                        	?>
	                            <tr>
	                                <td>{{ $no }}</td>
	                                <td>{{ $obj->obat->nama }}</td>
	                                <td class="text-right">{{ $obj->jumlah }}</td>
	                                <td class="text-right">{{ $harga_jual }}</td>
	                                <td class="text-right">{{ $diskon }}</td>
	                                <td class="text-right">{{ $subtotal_f }}</td>
	                            </tr>
	                            @endforeach
	                        </tbody>
	                    </table>
	                </div>
	                <!-- /.col -->
	            </div>
	            <!-- /.row -->
	            <div class="row">
	                <!-- accepted payments column -->
	                <div class="col-6">
	                    <p class="lead">Payment Methods:</p>
	                    <img src="{{asset('assets/dist/img/credit/visa.png')}}" alt="Visa">
	                    <img src="{{asset('assets/dist/img/credit/mastercard.png')}}" alt="Mastercard">
	                    <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
	                        Silakan melakukan pembayaran ke nomor rekening yang telah dicantumkan diatas.
	                    </p>
	                </div>
	                <!-- /.col -->
	                <div class="col-6">
	                    <!-- <p class="lead">Amount Due 2/22/2014</p> -->
	                    <?php 
	                    	$diskon = $penjualan->diskon_persen/100*$total;
	                    	$total = $total-$diskon;
	                    	$diskon_f = 'Rp '.number_format($diskon, 2);
	                    	$total_f = 'Rp '.number_format($total, 2);
	                    ?>
	                    <div class="table-responsive">
	                        <table class="table">
	                            <tr>
	                                <th width=80%">Total:</th>
	                                <td class="text-right">{{ $total_f }}</td>
	                            </tr>
	                            <tr>
	                                <th>Tax</th>
	                                <td class="text-right">Rp 0,00</td>
	                            </tr>
	                            <tr>
	                                <th>Shipping:</th>
	                                <td class="text-right">Rp 0,00</td>
	                            </tr>
	                            <tr>
	                                <th>Diskon ({{ $penjualan->diskon_persen }}%)</th>
	                                <td class="text-right">{{ $diskon_f }}</td>
	                            </tr>
	                            <tr>
	                                <th>Total:</th>
	                                <td class="text-right">{{ $total_f }}</td>
	                            </tr>
	                        </table>
	                    </div>
	                </div>
	                <!-- /.col -->
	            </div>
	            <!-- /.row -->
	            <!-- this row will not appear when printing -->
	            <div class="row no-print">
	                <div class="col-12">
	                    <a href="{{ url('penjualan/invoiceprint/'.$id) }}" rel="noopener" target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
	                    <button type="button" class="btn btn-success float-right"><i class="far fa-credit-card"></i> Submit
	                    Payment
	                    </button>
	                    <!-- <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
	                    <i class="fas fa-download"></i> Generate PDF
	                    </button> -->
	                    <a href="{{ url('penjualan/generatepdf/'.$id) }}" rel="noopener" target="_blank" class="btn btn-primary float-right"><i class="fas fa-print"></i> Generate PDF</a>
	                </div>
	            </div>
	        </div>
	        <!-- /.invoice -->
	    </div>
	    <!-- /.col -->
	</div>
	<!-- /.row -->
@endsection

@section('style')
@endsection

@section('script')
@endsection

