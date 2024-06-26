<style type="text/css">
    p {
      text-align: justify;
      text-justify: inter-word;
      font-size: 12pt;
    }
</style>
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<table  id="tb_quis" class="table table-bordered">
	<thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $no = 0;
        ?>
        
    	@foreach($rekaps as $p)
            <?php $no++;?>
       		<tr id="nama-{{ $p->id }}">
                <td><b>{{ $no }}.</b></td>
                <td>
                    <a href="#" class="xedit" data-pk="{{$p->id}}" data-name="nama">{{$p->nama}}</a>
                </td>
        	</tr>
        @endforeach
    </tbody>
</table>
<div style="margin: 0!important;padding: 0!important">
    <div class="pagination pagination-xs no-margin">Showing {{ $rekaps->firstItem() }} to {{ $rekaps->lastItem() }} of {{ $rekaps->total() }} entries</div>
    <div class="pagination pagination-xs no-margin pull-right">{{ $rekaps->links() }}</div>
</div>



<script type="text/javascript">
$(function() {
    $(".pagination a").click(function() {
    	var url = new URL($(this).attr('href'));
		var c = url.searchParams.get("page");
		post_this(c);
		return false;
    });
});


$(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{csrf_token()}}'
                    }
                });

                $('.xedit').editable({
                    url: '{{url("contacts/update")}}',
                    title: 'Update',
                    success: function (response, newValue) {
                        console.log('Updated', response)
                    }
                });

        })
        
</script>



