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
        <h4 class="modal-title">New portfolio summary</h4>
      </div>
      <div class="modal-body">

     <ul class="nav nav-tabs">
       <li class="active"><a data-toggle="tab" href="#setcategories">Set categories</a></li>
       <li id="tbedit"><a data-toggle="tab" href="#barchart">Bar chart inputs</a></li>
     </ul>

     <div class="tab-content">
         
         <div id="setcategories" class="tab-pane fade in active"> 
         
             <div>
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table-ctrls">
                         <tbody class="opt-list" style="padding:20px;"></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <button class="btn btn-primary b-add-category">Add category</button>
                    </div>
                </div>            
             </div>
             
         </div>
         
         <div id="barchart" class="tab-pane fade"> 
         </div>
         
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save model-update">Save</button>
      </div>
    </div>

  </div>
</div>
