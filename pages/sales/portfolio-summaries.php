<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>

<ul class="nav nav-tabs w-sumtabs">
  <li class="active"><a data-toggle="tab" href="#tabpflist">Portfolio list</a></li>
  <li id="tbedit"><a data-toggle="tab" href="#tabedit">Portfolio summaries</a></li>
  <li id="schart"><a data-toggle="tab" href="#tabschart">Summary charts: <span id="pfname"></span></a></li>
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
    
</div>

<form name="fprint" method="POST" target="_blank" action="<?=mkURL('/html.php/pages/sales/chart-mranking.pdf')?>">
  <input type="hidden" name="data" />
  <input type="hidden" name="title" />
</form>

<div id="editpfsum"></div>


