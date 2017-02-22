<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#tabpflist">Portfolio list</a></li>
  <li id="tbedit"><a data-toggle="tab" href="#tabedit">Portfolio summaries</a></li>
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

        <div id="mranking">
            <div class="row">
                <div class="col-lg-6"><?=$f->search('sic_id','sic')?></div>
                <div class="col-lg-4"><?=$f->modelSelect('region','/pages/sales/Model/regions')?></div>
                <div class="col-lg-2"><?=$f->input('minsize','number')?></div>
            </div>
            <div class="row">
                <div class="col-lg-7"><p id="sic_description"></p></div>
                <div class="col-lg-2"><?=$f->input('year','number')?></div>                
                <div class="col-lg-3">
                    <div class="btn-group">
                        <button style="margin-top:25px" class="btn btn-default b-csv">Download .CSV</button>
                        <button style="margin-top:25px" class="btn btn-primary b-print">Print</button>
                    </div>
                </div>
            </div>
            <div class="row" id="ranking">
            </div>
        </div>

    </div>
    
</div>

<form name="fprint" method="POST" target="_blank" action="<?=mkURL('/html.php/pages/sales/chart-mranking.pdf')?>">
  <input type="hidden" name="data" />
  <input type="hidden" name="title" />
</form>

<div id="editpfsum"></div>


