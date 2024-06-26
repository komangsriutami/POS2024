<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>APOTEKEREN | Invoice TO Print</title>
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    </head>
    <body>
        <div class="wrapper">
            <!-- Main content -->
            <section class="invoice">
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
	                        <strong>Apotek {{ $apotek1->group_apotek->nama_singkat }} {{ $apotek1->nama_panjang }}</strong><br>
	                        {{ $apotek1->alamat }}<br>
	                        Phone: {{ $apotek1->telepon }}<br>
	                        Email: {{ $apotek1->email }}
	                    </address>
	                </div>
	                <!-- /.col -->
	                <div class="col-sm-4 invoice-col">
	                    To
	                    <strong>Apotek {{ $apotek2->group_apotek->nama_singkat }} {{ $apotek2->nama_panjang }}</strong><br>
                        {{ $apotek2->alamat }}<br>
                        Phone: {{ $apotek2->telepon }}<br>
                        Email: {{ $apotek2->email }}
	                </div>
	                <!-- /.col -->
	                <div class="col-sm-4 invoice-col">
	                    <b>Invoice #{{ $transfer_outlet->id }}</b><br>
	                    <br>
	                    <b>Payment Due:</b> 2/22/2014<br>
	                    <b>Account:</b> 968-34567<br>
	                    <b>Account Name:</b> BCA 
	                </div>
	                <!-- /.col -->
	            </div>
	            <br>
	            <!-- /.row -->
	            <!-- Table row -->
	            <?php $total = 0; ?>
	            <div class="row">
	                <div class="col-12 table-responsive">
	                    <table class="table table-striped">
	                        <thead>
	                            <tr>
	                                <th width="2%">No</th>
	                                <th width="55%">Nama</th>
	                                <th width="8%" class="text-right">Jumlah</th>
	                                <th width="15%" class="text-right">Harga</th>
	                                <th width="20%" class="text-right">Subtotal</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        	<?php $no = 0; ?>
	                        	@foreach($detail_transfer_outlets as $obj)
	                        	<?php 
	                        		$no++; 
	                        		$harga_outlet = 'Rp '.number_format($obj->harga_outlet, 2);
	                        		$subtotal = $obj->jumlah*$obj->harga_outlet;
	                        		$total = $total + $subtotal;
	                        		$subtotal_f = 'Rp '.number_format($subtotal, 2);
	                        	?>
	                            <tr>
	                                <td>{{ $no }}</td>
	                                <td>{{ $obj->obat->nama }}</td>
	                                <td class="text-right">{{ $obj->jumlah }}</td>
	                                <td class="text-right">{{ $harga_outlet }}</td>
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
	                    	$total_f = 'Rp '.number_format($total, 2);
	                    ?>
	                    <div class="table-responsive">
	                        <table class="table">
	                            <tr>
	                                <th width="60%">Total:</th>
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
	                                <th>Total:</th>
	                                <td class="text-right">{{ $total_f }}</td>
	                            </tr>
	                        </table>
	                    </div>
	                </div>
	                <!-- /.col -->
	            </div>
	            <!-- /.row -->
            </section>
            <!-- /.content -->
        </div>
        <!-- ./wrapper -->
    </body>
</html>