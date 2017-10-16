<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<!-- Modal -->
<div class="modal fade middle" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Division</h4>
      </div>
      <div class="modal-body">

     <!-- Search form -->
     <div class="#editdivision">
        <div class="row">
            <div class="col-lg-12"><?=$f->input('me')?></div>
        </div>
        <div class="row">
            <div class="col-lg-4"><?=$f->input('sic','number')?></div>
        </div>
        <div class="row">
            <div class="col-lg-4"><?=$f->input('sales','number')?></div>
        </div>
     </div>
     <?php
        echo $f->hidden('syear','number');
        echo $f->hidden('division','number');
        echo $f->hidden('cid','text');
     ?>
      <!-- /Search form -->
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save model-update">Save</button>
      </div>
    </div>

  </div>
</div>
