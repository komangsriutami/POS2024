<?php
function printkasir($kode_jualcetak)
{
	$CI =& get_instance();
		/*-------------------PRINT--------------------*/
		//$handle= printer_open("Generic / Text Only");
		//$handle= printer_open("EPSON TM-U220 Receipt");
		//printer_set_option($handle, PRINTER_MODE, "RAW");
		//printer_start_doc($handle, "Nota Kasir Curative");
		
			//printer_start_page($handle);
	
		//if($CI->session->userdata('level_user') == 'kasir'){
		$CI->load->model('kasir/mjual');			
			//$date = Date("d/m/Y H:i:s", time()+60*60*7);
			
		$toko=$CI->mjual->print_datatoko($CI->session->userdata('kode_toko'));
		if($toko !=null) {
			foreach($toko as $r):
				$kode_toko=$r->kode_toko;
				$alamat_toko=$r->alamat_toko;
				$telpon_toko=$r->telpon_toko;
			endforeach;
		}
		
		if($kode_jualcetak==""){
			$no_jualakhir=$CI->mjual->print_no_jualakhir();
			if($no_jualakhir !=null) {
				foreach($no_jualakhir as $r):
					$kode_jualcetak=$r->kode_jual;
				endforeach;
			}
		}
		
		$jual_print=$CI->mjual->print_jual($kode_jualcetak);
		//print_r($jual_print);
		
			
			$ttlbelanja=0;
			$ke=0;
			if($jual_print !=null) {
				foreach($jual_print as $jp):				
					$tgl_juall=Date('d-m-Y',strtotime($jp->tgl_jual));
					//$waktu=explode(" ",$jp->waktu_jual);
					$waktu=$jp->jam_jual;
					$date=$tgl_juall." ".$waktu;
					
					$user_jual_t=$jp->user_jual;
					$user_jual_nm=$CI->mjual->print_user_jual($user_jual_t);
					if($user_jual_nm !=null) {
						foreach($user_jual_nm as $r):
							$user_jual_nmt=$r->nama_user;
						endforeach;
					}
					
					$user_bayarhutang=$jp->user_bayarhutang;
					$user_jual_nmbayarhutang=$CI->mjual->print_user_jual($user_bayarhutang);
					if($user_jual_nmbayarhutang !=null) {
						foreach($user_jual_nmbayarhutang as $uh):
							$user_jual_nmtbayarhutang=$uh->nama_user;
						endforeach;
					}
				
					if($ke==0){
						$a="\n".
							chr(27).chr(97).chr(1).chr(27).chr(33).chr(32)."Kolektiv \n".
							chr(27).chr(33).chr(0)."A Communal Maneuver \n".
							chr(27).chr(33).chr(0).$alamat_toko."\n".
							//chr(27).chr(33).chr(1)."Telp. ".$telpon_toko."\n".
							chr(27).chr(33).chr(1).$date."\n".
							chr(27).chr(33).chr(1)."Nota: ".$jp->nomor_jual."\n".
							chr(27).chr(33).chr(1)."----------------------------------------\n".

							chr(27).chr(33).chr(1);
						$ke=1;
					}			
			
					$kode_barang=$jp->barang_detiljual;
					$nama_barang_t=$CI->mjual->print_nama_barang_t($kode_barang);
					if($nama_barang_t !=null) {
						foreach($nama_barang_t as $r):
							$nama_barang=$r->nama_barang.' '.$r->warna.' '.$r->ukuran;
							$kategori_barang=$r->kategori_barang;
						endforeach;
					}
			
			
				
					$diskon=$jp->diskon_detiljual+$jp->diskon_member_jual+$jp->diskon_supplier;
					$hargaperbrg=($jp->harga_detiljual-($jp->harga_detiljual*($diskon/100)));
					$ttlperbrgs=$jp->jml_detiljual*$hargaperbrg;
					
					$ttlbelanja+=$ttlperbrgs-$jp->diskonnominal_detiljual;
			
					if($jp->diskon_detiljual>0 || $jp->diskon_member_jual>0 || $jp->diskon_supplier>0){
				
					$a=$a.
						str_pad($nama_barang, 40," ", STR_PAD_RIGHT)."\n ".					
						//str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",11," ", STR_PAD_LEFT)."\n ".
						str_pad(number_format($hargaperbrg, 0, '.', ','), 11," ", STR_PAD_LEFT).
						str_pad(" x ",3," ", STR_PAD_LEFT).
						str_pad(number_format($jp->jml_detiljual, 0, '.', ','),12," ", STR_PAD_RIGHT).
						str_pad("= ",3," ", STR_PAD_LEFT).str_pad(number_format($ttlperbrgs, 0, '.', ','),10," ", STR_PAD_LEFT)."\n".
						
						str_pad(" ", 23," ", STR_PAD_LEFT).	
						str_pad(" ",3," ", STR_PAD_LEFT).
						str_pad(" (diskon ".number_format($diskon, 0, '.', ',')."%)",10," ", STR_PAD_LEFT)."\n";
						
						
					} else {
				
			
				
						$a=$a.
							str_pad($nama_barang, 40," ", STR_PAD_RIGHT)."\n ".
							str_pad(number_format($hargaperbrg, 0, '.', ','), 11," ", STR_PAD_LEFT).
							str_pad(" x ",3," ", STR_PAD_LEFT).
							str_pad(number_format($jp->jml_detiljual, 0, '.', ','),12," ", STR_PAD_RIGHT).
							str_pad("= ",3," ", STR_PAD_LEFT).str_pad(number_format($ttlperbrgs, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
						}
		//}
		
						if($jp->diskonnominal_detiljual>0){
							
							$a=$a.str_pad($jp->ket_diskonnominal, 40," ", STR_PAD_RIGHT)."\n ".
							str_pad(" ", 11," ", STR_PAD_LEFT).
							str_pad(" ",3," ", STR_PAD_LEFT).
							str_pad(" ",12," ", STR_PAD_LEFT).
							str_pad(" ",3," ", STR_PAD_LEFT).str_pad("(".number_format($jp->diskonnominal_detiljual, 0, '.', ',').")",10," ", STR_PAD_LEFT)."\n";
											
							//$diskonnominal_detiljual+=$jp->diskonnominal_detiljual;
						}
					
					
						$card_fee=$jp->card_fee;
					
						endforeach;
					}		
			
			
			$a=$a.
				str_pad(" ",10," ", STR_PAD_LEFT).str_pad("-",30,"-", STR_PAD_LEFT)."\n".
				str_pad(" ", 27," ", STR_PAD_LEFT).
				str_pad(" ",3," ", STR_PAD_LEFT).str_pad(number_format($ttlbelanja, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
						
;			if($jp->service > 0){
				$nominal_sevice = ($jp->service/100)*$ttlbelanja;
				$a=$a.
					str_pad("SERVICE ".$jp->service." %", 27," ", STR_PAD_LEFT).
					str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($nominal_sevice, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
				$ttlbelanja += $nominal_sevice;
			}
			
;			if($jp->tax > 0){
				$nominal_tax = ($jp->tax/100)*$ttlbelanja;
				$a=$a.
					str_pad("TAX ".$jp->tax." %", 27," ", STR_PAD_LEFT).
					str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($nominal_tax, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
				$ttlbelanja += $nominal_tax;
			}
			
			
			if($jp->ongkir > 0){
				$a=$a.
					str_pad("ONGKIR", 27," ", STR_PAD_LEFT).
					str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($jp->ongkir, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
				$ttlbelanja += $jp->ongkir;
			}
			
			
			$a=$a.
				str_pad(" ",10," ", STR_PAD_LEFT).str_pad("-",30,"-", STR_PAD_LEFT)."\n".
				str_pad("TOTAL", 27," ", STR_PAD_LEFT).
				str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($ttlbelanja, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
				
				
			
			if($jp->cara_bayar=="cash"){
				$a=$a.
					str_pad("CASH", 27," ", STR_PAD_LEFT).
					str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($jp->bayar_jual, 0, '.', ','),10," ", STR_PAD_LEFT)."\n".
					str_pad("CHANGE", 27," ", STR_PAD_LEFT).
					str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($jp->kembali_jual, 0, '.', ','),10," ", STR_PAD_LEFT)."\n".
					str_pad(" ", 26," ", STR_PAD_LEFT).
					str_pad(" ",3," ", STR_PAD_LEFT).str_pad("(CASH)",10," ", STR_PAD_LEFT)."\n";
					
			} else if($jp->cara_bayar=="online"){
				$a=$a.
					str_pad(" ", 23," ", STR_PAD_LEFT).
					str_pad(" ",3," ", STR_PAD_LEFT).str_pad("(ONLINE ORDER)",10," ", STR_PAD_LEFT)."\n";
					
			} else {
				
				$nominal_card_fee=0;
				$totalplusfee=0;
				
				if($card_fee>0){
					$nominal_card_fee=($card_fee/100)*$ttlbelanja;					
					$totalplusfee=$ttlbelanja+$nominal_card_fee;
					
					$a=$a.
						str_pad("Card fee", 27," ", STR_PAD_LEFT).
						str_pad($card_fee."%",3," ", STR_PAD_LEFT).str_pad(number_format($nominal_card_fee, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
					
					$a=$a.
						str_pad(" ",10," ", STR_PAD_LEFT).str_pad("-",30,"-", STR_PAD_LEFT)."\n".
						str_pad("TOTAL", 27," ", STR_PAD_LEFT).
						str_pad(" = ",3," ", STR_PAD_LEFT).str_pad(number_format($totalplusfee, 0, '.', ','),10," ", STR_PAD_LEFT)."\n";
				}
				
				if ($jp->cara_bayar=="credit"){
					$a=$a."\n".
						str_pad(" ", 23," ", STR_PAD_LEFT).
						str_pad(" ",3," ", STR_PAD_LEFT).str_pad("(CREDIT CARD)",10," ", STR_PAD_LEFT)."\n";
														
						
				} elseif ($jp->cara_bayar=="debet"){
					$a=$a."\n".
						str_pad(" ", 23," ", STR_PAD_LEFT).
						str_pad(" ",3," ", STR_PAD_LEFT).str_pad("(DEBET CARD)",10," ", STR_PAD_LEFT)."\n";
				}			
			}			
			
					$a=$a.
						str_pad(" ", 17," ", STR_PAD_LEFT).
						str_pad(" ",3," ", STR_PAD_LEFT).str_pad("Cashier: ".$user_jual_nmt,19," ", STR_PAD_LEFT)."\n";						
						
			
			$b=$a.str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("THANK YOU FOR SHOPPING", 40,"-", STR_PAD_BOTH)."\n".str_pad("Instagram : @kolektiv", 40," ", STR_PAD_BOTH)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT)."\n".str_pad("",40," ", STR_PAD_LEFT);		
			
				
			
			//printer_write( $handle , $b);

					//printer_end_page($handle);

			//printer_end_doc($handle);

			//printer_close($handle);
		//}
			print_r($b) ;
		/*-------------------END PRINT--------------------*/
		
		
		/*SEMUA SAMA PADA FILE :
			=> cpelunasanhutang.php
			=> cjual
			=> ccetakulangnotakasir
			*/
}
?>