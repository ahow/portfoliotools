<?php
    $this->displayCrumbs();
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>

<ul class="nav nav-tabs w-sumtabs">
  <li class="active"><a data-toggle="tab" href="#tabpflist">Portfolio list</a></li>
  <li id="tbedit"><a data-toggle="tab" href="#spset">Summary page settings</a></li>
  <li id="tbedit"><a data-toggle="tab" href="#tabedit">Portfolio summaries</a></li>
  <li id="schart"><a data-toggle="tab" href="#tabschart">Summary charts: <span id="pfname"></span></a></li>
  <li id="ipsnap"><a data-toggle="tab" href="#psnap">Portfolio snapshot</a></li>
</ul>

<div class="tab-content"  style="padding-top: 15px;">
   
   <div id="tabpflist" class="tab-pane fade in active"> 
        <div class="model-list" data-model="/pages/sales/Model/portfolio">
            <table class="table table-striped selectable">
                <thead></thead>
                <tbody></tbody>
            </table>
            <div class="model-pager"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="spset">
         <div class="row">
             <div class="col-lg-6"><?=$f->modelSelect('social_value_metric','/pages/sales/Model/metric-lookup')?></div>
             <div class="col-lg-6"><?=$f->modelSelect('esg_score','/pages/sales/Model/metric-lookup')?></div>
         </div>
         <div id="metrics">
               <div class="row">
                    <div class="col-lg-12">
                        <table class="table-ctrls">
                         <thead><th>Metric</th><th>Min</th><th>Max</th></thead>
                         <tbody class="metrics-list" style="padding:20px;"></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <button class="btn btn-primary b-add-metric-row">Add metric</button>
                        <button class="btn btn-success b-save-settings">Save</button>
                    </div>
                    <div class="col-lg-6">
                    </div>
               </div>
         </div>
    </div>
    
    <div class="tab-pane fade" id="tabedit">
        <div class="row">
            <div class="model-list col-lg-12" data-model="/pages/sales/Model/portfolio-summaries">
                <table class="table table-striped selectable">
                    <thead></thead>
                    <tbody></tbody>
                </table>
                <div class="model-pager"></div>
            </div>            
        </div>
    </div>
    
    <div class="tab-pane fade" id="tabschart" >
        <div id="pfsummary"></div>        
    </div>

    <div class="tab-pane fade" id="psnap">
         <div id="ss_metrics">
              <div class="row">
                 <div class="col-lg-6"><?=$f->modelSelect('ss_comparison','/pages/sales/Model/portfolio-lookup')?></div>
               </div>
               <div class="row">
                    <div class="col-lg-12">
                        <table class="table-ctrls">
                         <thead><th>Metric</th></thead>
                         <tbody class="metrics-list" style="padding:20px;"></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <button class="btn btn-primary b-add-metric-row">Add metric</button>
                        <button class="btn btn-success b-save-settings">Save</button>
                    </div>
                    <div class="col-lg-6">
                    </div>
               </div>
         </div>
    </div>
    
</div>

<form name="fprint" method="POST" target="_blank" action="<?=mkURL('/html.php/pages/sales/chart-mranking.pdf')?>">
  <input type="hidden" name="data" />
  <input type="hidden" name="title" />
</form>

<div id="editpfsum"></div>


