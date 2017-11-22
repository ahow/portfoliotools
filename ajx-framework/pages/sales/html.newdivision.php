<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<!-- Modal -->
<div class="modal fade large" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">New division</h4>
      </div>
      <div class="modal-body w-division">

     <!-- Edit form -->   
         <div class="row">           
                 <div class="col-lg-4"><label>COMPANY Name</label></div>
                 <div class="col-lg-2"><?=$f->input('num')?></div>
                 <div class="col-lg-2"><?=$f->search3dot('sic_code','sic')?></div>
                 <div class="col-lg-4"><?=$f->input('me')?></div>
         </div>       
         <div class="row">           
                 <div class="col-lg-4"><?=$f->input('syear')?></div>
                 <div class="col-lg-2"><?=$f->input('sales')?></div>
                 <div class="col-lg-2"><?=$f->input('ebit')?></div>
                 <div class="col-lg-2"><?=$f->input('assets')?></div>
                 <div class="col-lg-2"><?=$f->input('capex')?></div>
         </div>       
     <!-- /Edit form -->

        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save-division disabled">Save</button>
      </div>
    </div>

  </div>
</div>
