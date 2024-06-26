<div class="row">
   <div class="col-sm-12">
      <div class="card card-info card-outline">
         <div class="card-body">
            <div class="row">
               <div class="form-group col-md-12">
                  @if($menu->error_log != '' OR $menu->error_log != null) 
                     {!! $menu->error_log !!}
                  @else
                     <p>Error book belum tersedia saat ini.</p>
                  @endif
               </div>
            </div>
         </div>
         <div class="card-footer">
            <button type="button" class="btn btn-danger w-md m-b-5 float-right" data-dismiss="modal"><i class="fa fa-undo"></i> Kembali</button>
         </div>
      </div>
   </div>
</div>