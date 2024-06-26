<style type="text/css">
    p {
      text-align: justify;
      text-justify: inter-word;
      font-size: 12pt;
    }
</style>
<table  id="tb_obat" class="table table-bordered table-striped table-hover">
    <thead>
        <tr class="bg-gray color-palette">
            <th width="5%">No.</th>
            <th width="8%">Barcode</th>
            <th width="30%"><!-- <a href="#" onclick="yuhuBy(2)">Nama</a> --> Nama</th>
            <th width="5%">UJ</th>
            <th width="5%">HB</th>
            <th width="7%">HB+ppn</th>
            <th width="7%">HJ</th>
            <th width="5%">%</th>
            <th width="7%"><b>Treshold</th>
            <th width="7%"><b>Hit HJ</b></th>
            <th width="5%">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $no = $currentPage*10;  
        ?>
        
        @foreach($obats as $p)
            <?php 
                $no++;
                $harga_beli = number_format($p->harga_beli,0,',','.');
                $harga_beli_ppn = number_format($p->harga_beli_ppn,0,',','.');
                $harga_jual = number_format($p->harga_jual,0,',','.');
                $harga_ambang_batas = number_format($p->harga_ambang_batas,0,',','.');
                $harga_jual_now = number_format($p->harga_jual_now,0,',','.');
                if ($no % 2 == 0){ 
                    $genap = 1;
                    $class = '';
                }else {
                    $genap = 0;
                    $class = 'bg-gray disabled color-palette';
                }
            ?>
            <tr class="{{ $class }}">
                <td>{{ $no }}</td>
                <td>{{ $p->barcode }}</td>
                <td>{{ $p->nama }}</td>
                <td>{{ $p->untung_jual }}%</td>
                <td style="text-align: right;">{{ $harga_beli }}</td>
                <td style="text-align: right;">{{ $harga_beli_ppn }}</td>
                <td style="text-align: right;">{{ $harga_jual }}</td>
                <td style="text-align: center;">{{ $persen }}</td>
                <td style="text-align: right;">{{ $harga_ambang_batas }}</td>
                <td style="text-align: right;">{{ $harga_jual_now }}</td>
                <td style="text-align: center;">
                    <?php
                        $id_user = Auth::user()->id;
                    ?>
                    @if($id_user == 1 || $id_user == 2 || $id_user == 16)
                    <span class="btn btn-primary btn-sm" onClick="setting_harga_jual({{ $p->id_obat }}, {{ $p->harga_beli }}, {{ $p->harga_beli_ppn }}, {{ $p->id }})" data-toggle="tooltip" data-placement="top" title="Edit Data"><i class="fa fa-edit"></i></span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div style="margin: 0!important;padding: 0!important">
    <div class="pagination pagination-xs no-margin">Showing {{ $obats->firstItem() }} to {{ $obats->lastItem() }} of {{ $obats->total() }} entries</div>
    <div class="pagination pagination-xs no-margin pull-right">{{ $obats->links() }}</div>
</div>

<script type="text/javascript">
$(function() {
    $(".pagination a").click(function() {
    	var url = new URL($(this).attr('href'));
		var c = url.searchParams.get("page");
        var d = 0;
		post_this(c);
		return false;
    });
});

function yuhuBy(id) {
    var d = id;
    post_this('', d);
    return false;
}
</script>