<section class="content">
    <div class="row">
        <div class="col-12" id="accordion">
            <?php $i = 0;?>
            @foreach($pengumumans as $obj)
                <?php 
                    $i++; 
                    //$link = url(Storage::url($obj->file));
                    $link = $obj->file;

                    $tanggal = $obj->created_at; 
                    if($obj->id_asal_pengumuman == 1) {
                        $asal = "Administrator";
                    } else if($obj->id_asal_pengumuman == 2) {
                        $asal = "Manajemen";
                    } else {
                        $asal = "Kepala Outlet";
                    }
                ?>

                <div class="callout callout-warning">
                    <h5>{{ $obj->judul }} <span style="font-size: 8pt;"> | <cite>tanggal : {{ $tanggal }} | oleh : {{ $asal }}</cite></span></h5> 

                    <p>{!! $obj->isi !!} </p>

                    @if(!is_null($obj->file))
                        <?php $id_en = Crypt::encryptString($obj->id); ?>
                        <?php $jenis_encrypt = Crypt::encryptString('pengumuman'); ?>
                        <?php $filename = Crypt::encryptString($obj->file); ?>
                        <a href={{ url("fileaccess") }}/{{$id_en}}/{{ $jenis_encrypt }}/{{ $filename }} target="_blank" class="btn btn-sm btn-outline-warning text-warning">[ lihat file ]</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<div style="margin: 0!important;padding: 0!important">
    <div class="pagination pagination-xs no-margin">Showing {{ $pengumumans->firstItem() }} to {{ $pengumumans->lastItem() }} of {{ $pengumumans->total() }} entries</div>
    <div class="pagination pagination-xs no-margin pull-right">{{ $pengumumans->links() }}</div>
</div>

<script type="text/javascript">
$(function() {
    $(".pagination a").click(function() {
    	var url = new URL($(this).attr('href'));
		var c = url.searchParams.get("page");
		get_pengumuman(c);
		return false;
    });
});
</script>