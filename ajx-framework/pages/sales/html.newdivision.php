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
                 <div class="col-lg-5"><label class="w-company-name">COMPANY Name</label></div>
                 <div class="col-lg-2"><?=$f->input('num')?></div>
                 <div class="col-lg-5"><?=$f->input('me')?></div>
         </div>
         <table class="table table-striped">
           <thead>
             <tr>
                  <th>Year</th>
                  <th>Industry details</th>
                  <th>Sales</th>
                  <th>Ebit</th>
                  <th>Assets</th>
                  <th>Capex</th>
             </tr>
            </thead>
            <tbody class="w-entry-body">
                <tr class="entry">
                    <td class="col-xs-2">
                       <div class="input-group">
                                  <span class="input-group-btn">
                                      <button class="btn btn-success btn-add" type="button">
                                        <span class="glyphicon glyphicon-plus"></span>
                                      </button>
                                  </span>
                                  <input type="text " class="form-control" data-control-type="basic" id="syear" placeholder="Year">
                        </div>  
                    </td>
                    <td name="sic"><a class="w-select-sic" href="javascript:">[Select SIC...]</a></td>
                    <td name="sales" contenteditable="true"></td>
                    <td name="ebit" contenteditable="true"></td>
                    <td name="assets" contenteditable="true"></td>
                    <td name="capex" contenteditable="true"></td>
                </tr>
                
            </tbody>
         </table>
     <!-- /Edit form -->

        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save-division disabled">Save</button>
      </div>
    </div>

  </div>
</div>
