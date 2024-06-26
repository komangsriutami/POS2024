$(document).ready(function(){


 	var get = [];
	location.search.replace('?','').split('&').forEach(function(val){
		split = val.split("=",2);
		get[split[0]] = split[1];
	});


	var url = window.location;
	//hanya akan bekerja pada href string yg matches dengan lokasi
	$('ul li a[href="'+ url +'"]').parent().addClass('active');

	$('ul .treeview .treeview-menu li a[href="'+ url +'"]').parent().addClass('active');

	$('ul .treeview .treeview-menu li a[href="'+ url +'"]').parent().addClass('aktif');

	$('.aktif').parent('.treeview-menu').parent('.treeview').addClass('active');

	$('.aktif').parent('.treeview-menu').addClass('menu-open');

	$('.aktif').parent('.treeview-menu').css('display','block');
		
 var tbjalurmasuk = $('#tbjalurmasuk').dataTable( {
				 processing: true,
		        serverSide: true,
		        ajax: 'tbjalurmasuk',
		        columns: [
		            {data: 'no', name: 'no',width:"2%"},
		            {data: 'jalur_masuk', name: 'jalur_masuk'},
		            {data: 'flag_lokal', name: 'flag_lokal'},
		            {data: 'flag_pasca', name: 'flag_pasca'},
		            {data: 'action', name: 'id',orderable: false, searchable: false}
		        ]

 } );
				
	$('#tbjalurmasuk_filter input').unbind();
	$('#tbjalurmasuk_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbjalurmasuk.fnFilter(this.value);
 	 }
}); 


 var tbperiode = $('#tbperiode').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbperiode',
		        columns: [
		            {data: 'no', name: 'no',width:"2%"},
		            {data: 'jalur_masuk', name: 'jalur_masuk'},
		            {data: 'angkatan', name: 'angkatan'},
		            {data: 'awal', name: 'awal'},
		            {data: 'akhir', name: 'akhir'},
		            {data: 'link_pengumuman', name: 'link_pengumuman'},
		            {data: 'tgl_pengumuman', name: 'tgl_pengumuman'},
		            {data: 'aktif', name: 'aktif'},
		            {data: 'action', name: 'id',orderable: false, searchable: false}
		        ]

 } );
				
	$('#tbjalurmasuk_filter input').unbind();
	$('#tbjalurmasuk_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbjalurmasuk.fnFilter(this.value);
 	 }
}); 


var tbnominal = $('#tbnominal').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbnominal',
		        columns: [
		            {data: null, name: null,width:"2%",searchable: false},
		            {data: 'jalur_masuk', name: 'jalur_masuk'},
		            {data: 'angkatan', name: 'angkatan'},
		            {data: 'nama_fakultas', name: 'nama_fakultas'},
		            {data: 'nama_jurusan', name: 'nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'keterangan', name: 'u.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'presentase_mhs', name: 'presentase_mhs'},
		            {data: 'action', name: 'id',orderable: false, searchable: false}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
				  order: [[ 1, "asc" ]]

 } );
				
	$('#tbnominal_filter input').unbind();
	$('#tbnominal_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbnominal.fnFilter(this.value);
 	 }
}); 



var tbproditawar = $('#tbproditawar').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbproditawar',
		        columns: [
		            {data: null, name: null,width:"2%",searchable: false},
		            {data: 'jalur_masuk', name: 'jalur_masuk'},
		            {data: 'angkatan', name: 'angkatan'},
		            {data: 'nama_jurusan', name: 'nama_jurusan'},
		            {data: 'konsentrasi_ps', name: 'konsentrasi_ps'},
		            {data: 'keterangan', name: 'keterangan'},
		            {data: 'status', name: 'is_reguler'},
		            {data: 'sks', name: 'is_sks'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
				  order: [[ 1, "asc" ]]

 } );
				
	$('#tbproditawar_filter input').unbind();
	$('#tbproditawar_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbproditawar.fnFilter(this.value);
 	 }
}); 


 var tbMahasiswa = $('#tbMahasiswa').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbmahasiswa',
		        columns: [
		            {data: null, name: null,width:"2%", searchable: false},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'keterangan', name: 'j.keterangan'},
		            {data: 'status', name: 'is_reguler'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt', name: 'mu.keterangan'},
		            {data: 'nominal', name: 'uk.nominal'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 1, "asc" ]]

 } );
				
	$('#tbMahasiswa_filter input').unbind();
	$('#tbMahasiswa_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbMahasiswa.fnFilter(this.value);
 	 }
}); 


 var tbKwitansi = $('#tbKwitansi').dataTable( {
				processing: true,
		        serverSide: true,
		        DisplayLength: 15,
		        ajax: 'tbkwitansi',
		        columns: [
		            {data: null, name: null,width:"2%", searchable: false},
		            {data: 'antrian_bank', name: 'antrian_bank'},
		            {data: 'tahun_ajaran', name: 'k.tahun_ajaran'},
		            {data: 'semester', name: 'k.semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt_keterangan', name: 'ukt.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'status_trx', name: 'k.status_trx'},
		            {data: 'action', name: 'k.id',orderable: false, searchable: false}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 1, "asc" ],[2,'asc']]

 } );
				
	$('#tbKwitansi_filter input').unbind();
	$('#tbKwitansi_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbKwitansi.fnFilter(this.value);
			$('#cetakTagihan').attr('href','cetak/tagihan/'+this.value);
 	 }
});


 var tbRiwayat = $('#tbRiwayat').dataTable( {
				processing: true,
		        serverSide: true,
		        DisplayLength: 15,
		        ajax:{
				        url: 'tbRiwayat',
				        data:function(d){
				        		if($('#tbRiwayat_filter input').val()!=""){
					        		d.nim = $('#tbRiwayat_filter input').val();
				        		}else{
					        		d.nim = $('#nim').val();	
				        		}
					         }
				     },
		        columns: [
		            {data: null, name: null,width:"2%", searchable: false},
		            {data: 'tahun_ajaran', name: 'k.tahun_ajaran'},
		            {data: 'semester', name: 'k.semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt_keterangan', name: 'ukt.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'trx_status', name: 'k.trx_status'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 3, "asc" ],[1,'asc']]

 } );
				
	$('#tbRiwayat_filter input').unbind();
	$('#tbRiwayat_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbRiwayat.fnFilter(this.value);
			// $('#cetakTagihan').attr('href','cetak/tagihan/'+this.value);
 	 }
});


 var tbLaporanMahasiswa = $('#tbLaporanMahasiswa').dataTable( {
				processing: true,
		        serverSide: true,
		        DisplayLength: 15,
		        ajax:{
				        url: 'tbLaporanMahasiswa',
				        data:function(d){
					        	d.id_jurusan = $('#id_jurusan').val();
					        	d.id_fakultas = $('#id_fakultas').val();
					        	d.tgl_awal = $('#tgl_awal').val();
					        	d.tgl_akhir = $('#tgl_akhir').val();
					         }
				     },
		        columns: [
		            {data: null, name: null,width:"2%"},
		            {data: 'tahun_ajaran', name: 'tahun_ajaran'},
		            {data: 'semester', name: 'semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'jurusan', name: 'jurusan'},
		            {data: 'jenis', name: 'jenis'},
		            {data: 'golongan', name: 'golongan'},
		            {data: 'total_spp', name: 'total_spp'},
		            {data: 'total_sdpp', name: 'total_sdpp'},
		            {data: 'total_ajbmm', name: 'total_ajbmm'},
		            {data: 'total_terbayar', name: 'k.total_terbayar'},
		            {data: 'status_trx', name: 'k.status_trx'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 5, "asc" ]]

 } );
				
	$('#tbLaporanMahasiswa_filter input').unbind();
	$('#tbLaporanMahasiswa_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbLaporanMahasiswa.fnFilter(this.value);
			//$('#rincianExcel').attr('href','rincian/excel/'+this.value);
 	 }
});


var tbLaporanProdi = $('#tbLaporanProdi').dataTable( {
				processing: true,
		        serverSide: true,
		        DisplayLength: 15,
		        ajax:{
				        url: 'tbLaporanProdi',
				        data:function(d){
				        		d.id_jurusan = $('#id_jurusan').val();
					        	d.id_fakultas = $('#id_fakultas').val();
					        	d.tgl_awal = $('#tgl_awal').val();
					        	d.tgl_akhir = $('#tgl_akhir').val();
					         }
				     },
		        columns: [
		            {data: null, name: null,width:"2%"},		            
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'jurusan', name: 'jurusan'},
		            {data: 'jenis', name: 'jenis'},
		            {data: 'golongan', name: 'golongan'},
		            {data: 'total_spp', name: 'total_spp'},
		            {data: 'total_sdpp', name: 'total_sdpp'},
		            {data: 'total_ajbmm', name: 'total_ajbmm'},
		            {data: 'total_terbayar', name: 'k.total_terbayar'},
		            {data: 'status_trx', name: 'k.status_trx'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 5, "desc" ]]

 } );
				
	$('#tbLaporanProdi_filter input').unbind();
	$('#tbLaporanProdi_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbLaporanProdi.fnFilter(this.value);
			//$('#rincianExcel').attr('href','rincian/excel/'+this.value);
 	 }
});


var tbLaporanFakultas = $('#tbLaporanFakultas').dataTable( {
				processing: true,
		        serverSide: true,
		        DisplayLength: 15,
		        ajax:{
				        url: 'tbLaporanFakultas',
				        data:function(d){
					        	d.id_fakultas = $('#id_fakultas').val();
					        	d.tgl_awal = $('#tgl_awal').val();
					        	d.tgl_akhir = $('#tgl_akhir').val();
					         }
				     },
		        columns: [
		            {data: null, name: null,width:"2%"},		            
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'jenis', name: 'jenis'},
		            {data: 'golongan', name: 'golongan'},
		            {data: 'total_spp', name: 'total_spp'},
		            {data: 'total_sdpp', name: 'total_sdpp'},
		            {data: 'total_ajbmm', name: 'total_ajbmm'},
		            {data: 'total_terbayar', name: 'k.total_terbayar'},
		            {data: 'status_trx', name: 'k.status_trx'}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 5, "desc" ]]

 } );
				
	$('#tbLaporanProdi_filter input').unbind();
	$('#tbLaporanProdi_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbLaporanProdi.fnFilter(this.value);
			//$('#rincianExcel').attr('href','rincian/excel/'+this.value);
 	 }
});



 var tbAntrian = $('#tbAntrian').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbAntrian',
		        columns: [
		            {data: 'no', name: 'no',width:"2%"},
		            {data: 'nomor_pembayaran', name: 'nomor_pembayaran'},
		            {data: 'nama', name: 'nama'},
		            {data: 'nama_fakultas', name: 'nama_fakultas'},
		            {data: 'nama_prodi', name: 'nama_prodi'},
		            {data: 'strata', name: 'strata'},
		            {data: 'total_nilai_tagihan', name: 'total_nilai_tagihan'},
		        ]

 } );
				
	$('#tbAntrian_filter input').unbind();
	$('#tbAntrian_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbAntrian.fnFilter(this.value);
 	 }
});


var tbclose = $('#tbclose').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbclose',
		        columns: [
		            {data: null, name: null,width:"2%",searchable: false},
		            {data: 'tahun_ajaran', name: 'k.tahun_ajaran'},
		            {data: 'semester', name: 'k.semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt_keterangan', name: 'ukt.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'status_trx', name: 'k.status_trx'},
		            {data: 'action', name: 'k.id',orderable: false, searchable: false}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 1, "asc" ]]

 } );
				
	$('#tbclose_filter input').unbind();
	$('#tbclose_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbclose.fnFilter(this.value);
			//$('#cetakTagihan').attr('href','cetak/tagihan/'+this.value);
 	 }
});


var tbpenurunan = $('#tbpenurunan').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbpenurunan',
		        columns: [
		            {data: null, name: null,width:"2%",searchable: false},
		            {data: 'tahun_ajaran', name: 'k.tahun_ajaran'},
		            {data: 'semester', name: 'k.semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt_keterangan', name: 'ukt.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'ukt_keterangan2', name: 'ukt2.keterangan'},
		            {data: 'nominal2', name: 'nominal2'},
		            {data: 'status_trx', name: 'k.status_trx'},		            
		            {data: 'action', name: 'k.id',orderable: false, searchable: false}
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 1, "asc" ]]

 } );
				
	$('#tbpenurunan_filter input').unbind();
	$('#tbpenurunan_filter input').bind('keyup', function(e) {
   if(e.keyCode == 13) {
			tbpenurunan.fnFilter(this.value);
			//$('#cetakTagihan').attr('href','cetak/tagihan/'+this.value);
 	 }
});  




var tbpenurunanValid = $('#tbpenurunanValid').dataTable( {
				processing: true,
		        serverSide: true,
		        ajax: 'tbpenurunanValid',
		        scrollX : true,
		        columns: [
		            {data: null, name: null,width:"2%",searchable: false},
		            {data: 'tahun_ajaran', name: 'k.tahun_ajaran'},
		            {data: 'semester', name: 'k.semester'},
		            {data: 'nim', name: 'm.nim'},
		            {data: 'nama', name: 'm.nama'},
		            {data: 'nama_fakultas', name: 'f.nama_fakultas'},
		            {data: 'nama_jurusan', name: 'js.nama_jurusan'},
		            {data: 'status_studi', name: 'status_studi'},
		            {data: 'status_mahasiswa', name: 's.keterangan'},
		            {data: 'ukt_keterangan2', name: 'ukt2.keterangan'},
		            {data: 'nominal2', name: 'nominal2'},
		            {data: 'ukt_keterangan', name: 'ukt.keterangan'},
		            {data: 'nominal', name: 'nominal'},
		            {data: 'status_trx', name: 'k.status_trx'},
		            {data: 'status_kirim', name: 'k.will_change'}		            
		        ],
		        rowCallback: function( row, data, iDisplayIndex ) {
		        	 var api = this.api();
				     var info = api.page.info();
				     var page = info.page;
				     var length = info.length;
				     var index = (page * length + (iDisplayIndex +1));
				     $('td:eq(0)', row).html(index);
				  },
		        order: [[ 1, "asc" ]]

 } );
				
	$('#tbpenurunanValid_filter input').unbind();
	$('#tbpenurunanValid_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13) {
			tbpenurunanValid.fnFilter(this.value);
			//$('#cetakTagihan').attr('href','cetak/tagihan/'+this.value);
 	 }
});  



$('#tgl_awal').change(function(){
	tbLaporanMahasiswa.api().ajax.reload();
	$('#exportRincian').attr('href','exportRincian?tgl_awal='+this.value+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanProdi.api().ajax.reload();
	$('#exportRekap').attr('href','exportRekapProdi?tgl_awal='+this.value+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanFakultas.api().ajax.reload();
	$('#exportRekapFakultas').attr('href','exportRekapFakultas?tgl_awal='+this.value+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
});
$('#tgl_akhir').change(function(){
	tbLaporanMahasiswa.api().ajax.reload();
	$('#exportRincian').attr('href','exportRincian?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+this.value+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanProdi.api().ajax.reload();
	$('#exportRekap').attr('href','exportRekapProdi?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanFakultas.api().ajax.reload();
	$('#exportRekapFakultas').attr('href','exportRekapFakultas?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
});
$('#id_jurusan').change(function(){
		tbLaporanMahasiswa.api().ajax.reload();
	$('#exportRincian').attr('href','exportRincian?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanProdi.api().ajax.reload();
	$('#exportRekap').attr('href','exportRekapProdi?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanFakultas.api().ajax.reload();
	$('#exportRekapFakultas').attr('href','exportRekapFakultas?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
});

$('#id_fakultas').change(function(){
		tbLaporanMahasiswa.api().ajax.reload();
	$('#exportRincian').attr('href','exportRincian?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanProdi.api().ajax.reload();
	$('#exportRekap').attr('href','exportRekapProdi?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
	tbLaporanFakultas.api().ajax.reload();
	$('#exportRekapFakultas').attr('href','exportRekapFakultas?tgl_awal='+$("#tgl_awal").val()+'&tgl_akhir='+$("#tgl_akhir").val()+'&id_jurusan='+$("#id_jurusan").val()+'&id_fakultas='+$("#id_fakultas").val());
});
    
$(document).on('submit', '#tambahjalurmasuk', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("tambahjalurmasuk", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbjalurmasuk.api().ajax.reload();
					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });



$(document).on('submit', '#tambahPeriode', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("tambahperiode", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbperiode.api().ajax.reload();
					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#tambahproditawar', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("tambahproditawar", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbproditawar.api().ajax.reload();
					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#tambahMahasiswa', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("tambahMahasiswa", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbMahasiswa.api().ajax.reload();
					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#tampilRiwayat', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("getRiwayat", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					
					tbRiwayat.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('#tbRiwayat_filter input').val($('#nim').val());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });

$(document).on('click', '#simpanRiwayat', function() {
  // alert("");
	  var id = $('#tbRiwayat_filter input').val();
	  $('.overlay').css('display','block');
	  $.ajax({
	    url: "simpanRiwayat/"+id,
	    success: function(result){
	    	 	tbRiwayat.api().ajax.reload();
				setTimeout(function() {
						// alert("data");
						$('.overlay').css('display','none');
						$("#infotambah").fadeIn(300);
				}, 1000);
				setTimeout(function() {
						$("#infotambah").fadeOut(2500);
				}, 2500);
	      }
	  });
});


$(document).on('submit', '#tambahkwitansi', function() {
		// post the data from the form
		$('#modaltambah').modal('hide');
		$('.overlay').css('display','block');
		$.get("tambahkwitansi", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbKwitansi.api().ajax.reload();
					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });

$(document).on('submit', '#perubahanUkt', function() {
		// post the data from the form
		$('#modalPerubahan').modal('hide');
		$('.overlay').css('display','block');
		$.get("perubahanUkt", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbKwitansi.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#closeTagihan', function() {
		// post the data from the form
		$('#modalClose').modal('hide');
		$('.overlay').css('display','block');
		$.get("closeTagihan", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbKwitansi.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#refundTagihan', function() {
		// post the data from the form
		$('#modalRefund').modal('hide');
		$('.overlay').css('display','block');
		$.get("refundTagihan", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbKwitansi.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });



$(document).on('submit', '#validCloseUkt', function() {
		// post the data from the form
		$('#modalValidClose').modal('hide');
		$('.overlay').css('display','block');
		$.get("validCloseUkt", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbclose.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });

$(document).on('submit', '#validPenurunanUkt', function() {
		// post the data from the form
		$('#modalValidPenurunan').modal('hide');
		$('.overlay').css('display','block');
		$.get("validPenurunanUkt", $(this).serialize())
			.done(function(data) {
				// 'data' is the text returned, you can do any conditions based on that
					tbpenurunan.api().ajax.reload();

					setTimeout(function() {
							//alert($("#reloadJs").html());
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
	 			
        return false;
    });


$(document).on('submit', '#ubahjalurmasuk', function() {
		$('#modalUbah').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#id").val();
		$.get("ubahjalurmasuk/"+id+"/edit", $(this).serialize())
			.done(function(data) {
					tbjalurmasuk.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
        return false;
});

$(document).on('submit', '#ubahperiode', function() {
		$('#modalUbahPeriode').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#id").val();
		$.get("ubahperiode/"+id+"/edit", $(this).serialize())
			.done(function(data) {
					tbperiode.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
        return false;
});


$(document).on('submit', '#kirimPenurunan', function() {
		$('#modalkirimPenurunan').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#kirimnim").val();
		$.get("kirimAllPenurunan/menunggu/edit", $(this).serialize())
			.done(function(data) {
					tbpenurunanValid.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
        return false;
});


$(document).on('submit', '#kirimH2h', function() {
		$('#modalkirim').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#kirimnim").val();
		$.get("kirimh2h/"+id+"/edit", $(this).serialize())
			.done(function(data) {
					tbKwitansi.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
        return false;
});

$(document).on('submit', '#kirimAll', function() {
	if($('#tbKwitansi_filter input').val()){
		$('#modalkirim').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#kirimnim").val();
		$.get("kirimh2h/"+id+"/edit", $(this).serialize())
			.done(function(data) {
					tbKwitansi.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
        
    }else{
    	$('#modalkirim').modal('hide');
		$('.overlay').css('display','block');
		var id = $("#kirimnim").val();
		$.get("kirimh2hAll/all/edit", $(this).serialize())
			.done(function(data) {
					tbKwitansi.api().ajax.reload();
					setTimeout(function() {
							$('.overlay').css('display','none');
							$("#infotambah").fadeIn(300);
					}, 1000);
					setTimeout(function() {
							$("#infotambah").fadeOut(2500);
					}, 2500);
			});
    }
    return false;
});	

$('#pembuatanKwitansi').on('change',function(){
	if(this.value == '2'){
		$('#buatKwitansi').removeClass('sembunyi');
		$('#buatKwitansi').addClass('tampil');
	}else{
		$('#buatKwitansi').removeClass('tampil');
		$('#buatKwitansi').addClass('sembunyi');
	}
});

	
$('#modalUbah').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi').load('jalurmasuk/'+ id +'/edit');
});

$('#modalUbahPeriode').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi').load('periode/'+ id +'/edit');
});

$('#modalClose').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi').load('tagihan/'+ id +'/edit');
});


$('#modalRefund').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi4').load('refund/'+ id +'/edit');
});

$('#modalValidClose').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi').load('validClose/'+ id +'/edit');
});

$('#modalValidPenurunan').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi').load('validPenurunan/'+ id +'/edit');
});

$('#modalkirim').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	if($('#tbKwitansi_filter input').val()){
	  	$('.overlay2').css('display','block');
	  	var id = $('#tbKwitansi_filter input').val();
	  	$('#isi3').load('getKirim/'+ id +'/edit');
  	}else{
  		$('.overlay2').css('display','block');
	  	var id = $('#tbKwitansi_filter input').val();
	  	$('#isi3').load('getKirimAll/edit');
  	}
});


$('#modalPerubahan').on('shown.bs.modal', function (e) {
  	//$('#id_jalur_masuk').val($(e.relatedTarget).data('id'));
  	$('.overlay2').css('display','block');
  	var id = $(e.relatedTarget).data('id');
  	$('#isi2').load('perubahan/'+ id +'/edit');
});


$('#jenisPembayaran').on('change',function(){
	if(this.value == '1' || this.value == '3'){
		$('#pembayaranTambahan').removeClass('sembunyi');
		$('#pembayaranTambahan').addClass('tampil');
	}else{
		$('#pembayaranTambahan').removeClass('tampil');
		$('#pembayaranTambahan').addClass('sembunyi');
	}
});



$('#nim').on('focusout',function(){
	$.ajax({url: "getmahasiswa/"+this.value,
		dataType : "json", 
		success: function(result){
        	$('#nama').val(result.nama);
        	$('#jurusan').val(result.nama_jurusan);
        	$('#konsentrasi').val(result.konsentrasi);
    	}
	});
});



$('#jalurmasuk').change(function(){
  var id = $('#nim').val();
  var ukt = $('#jenisUkt').val();
  var jalurMasuk = $('#jalurmasuk').val();
  $.ajax({
    url: "cekuktBaru/"+id+"/edit/"+ukt+"/jalurMasuk/"+jalurMasuk,
    dataType : "json", 
    success: function(result){
    	 if(result.nominal){
	          $("#nominalukt").val(result.nominal);
	          $("#idnominalukt").val(result.idnominalukt);
	          $("#id_prodi_tawar").val(result.id_prodi_tawar);
         }else{
	          $("#nominalukt").val("");
	          $("#idnominalukt").val("");
         }
      }
  });
});


$('#jenisUkt').change(function(){
  var id = $('#nim').val();
  var ukt = $('#jenisUkt').val();
  var jalurMasuk = $('#jalurmasuk').val();
  $.ajax({
    url: "cekuktBaru/"+id+"/edit/"+ukt+"/jalurMasuk/"+jalurMasuk,
    dataType : "json", 
    success: function(result){
    	 if(result.nominal){
	          $("#nominalukt").val(result.nominal);
	          $("#idnominalukt").val(result.idnominalukt);
	          $("#id_prodi_tawar").val(result.id_prodi_tawar);
         }else{
	          $("#nominalukt").val("");
	          $("#idnominalukt").val("");
         }
      }
  });
});


$('.date').datepicker({
        format: "yyyy/mm/dd",
        startDate: "2000-01-01",
        endDate: "2050-01-01",
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true
});

$('#jenisPeriode').change(function(){
	$.ajax({
		url: "ajaxPeriode/"+this.value,
		dataType : "json",
		success: function(result){
			$('#jenisJenjang').empty();
			  $('#jenisJenjang').append("<option value='' >Pilih Jenjang Studi</option>");                    
            $.each(result, function(key, value) {
                $('#jenisJenjang').append("<option value='" + key +"'>" + value + "</option>");
            });
		} 
	});
});

$('#jenisJenjang').change(function(){
	$.ajax({
		url: "ajaxJenjang/"+this.value,
		dataType : "json",
		success: function(result){
			$('#jenisJurusan').empty();
			  $('#jenisJurusan').append("<option value='' >Pilih Jurusan</option>");                    
            $.each(result, function(key, value) {
                $('#jenisJurusan').append("<option value='" + key +"'>" + value + "</option>");
            });
		} 
	});
});

$('#jenisJurusan').change(function(){
	$.ajax({
		url: "ajaxKonsentrasi/"+this.value,
		dataType : "json",
		success: function(result){
			$('#jenisKonsentrasi').empty();
			  $('#jenisKonsentrasi').append("<option value='' >Pilih Konsentrasi</option>");                    
            $.each(result, function(key, value) {
                $('#jenisKonsentrasi').append("<option value='" + key +"'>" + value + "</option>");
            });
		} 
	});
});


}); // end of doc ready


