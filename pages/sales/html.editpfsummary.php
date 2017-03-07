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
        <h4 class="modal-title">New portfolio summary <span class="pfname" style="font-weight:bold"></span></h4>
      </div>
      <div class="modal-body">

      <div class="row">           
           <div class="col-lg-12"><?=$f->textarea('description')?></div>
      </div>

     <ul class="nav nav-tabs">
       <li class="active"><a data-toggle="tab" href="#setcategories">Set categories</a></li>
       <li><a data-toggle="tab" href="#commsett">Common chart settings</a></li>
       <li><a data-toggle="tab" href="#barchart">Bar chart inputs</a></li>
       <li><a data-toggle="tab" href="#linechart">Line chart inputs</a></li>
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

         <div id="commsett" class="tab-pane fade">
             <div class="row" style="padding:20px;">           
                 <div class="col-lg-12"><?=$f->modelSelect('comparison','/pages/sales/Model/portfolio-lookup')?></div>
             </div>         
         </div>
                  
         <div id="barchart" class="tab-pane fade">
              <div class="row">           
                 <div class="col-lg-12"><?=$f->input('bar_title')?></div>
              </div>         
              <div class="row">
                    <div class="col-lg-12">
                        <table class="table-ctrls bar-chart t-bordered">
                         <thead><th>Series</th></thead>
                         <tbody style="padding:20px;"></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <button class="btn btn-primary b-add-bar-row">Add row</button>
                        <button class="btn btn-primary b-add-bar-column">Add column</button>
                    </div>
                </div>
         </div>

         <div id="linechart" class="tab-pane fade">
              <div class="row">           
                 <div class="col-lg-12"><?=$f->input('line_title')?></div>
              </div>   
              <div class="row">
                    <div class="col-lg-12">
                        <table class="table-ctrls line-chart t-bordered">
                         <thead><th>Series</th></thead>
                         <tbody style="padding:20px;"></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <button class="btn btn-primary b-add-line-row">Add row</button>
                        <button class="btn btn-primary b-add-line-column">Add column</button>
                    </div>
                </div>
         </div>
                  
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-save model-update">Save</button>
      </div>
    </div>

  </div>
</div>
