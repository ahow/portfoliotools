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
        <h4 class="modal-title">Edit divisions</h4>
      </div>
      <div class="modal-body w-division">

     <!-- Edit form -->   
         <div class="row">           
                 <div class="col-lg-5"><label class="w-company-name">COMPANY Name</label></div>
                 <div class="col-lg-2"><label>#<span class="w-division"></span></label></div>
                 <div class="col-lg-5"></div>
         </div>
         <table class="table table-striped">
           <thead>
             <tr>
                  <th>Year</th>
                  <th>Me</th>
                  <th>Industry details</th>
                  <th>Sales</th>
                  <th>Ebit</th>
                  <th>Assets</th>
                  <th>Capex</th>
             </tr>
            </thead>
            <tbody class="w-entry-body">
            </tbody>
         </table>
     <!-- /Edit form -->

        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save-division">Save</button>
      </div>
    </div>

  </div>
</div>
