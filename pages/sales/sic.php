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
  <li class="active"><a data-toggle="tab" href="#tabsearh">Search</a></li>
  <li id="tbedit" class="disabled"><a data-toggle="tab" href="#tabedit">Edit</a></li>
</ul>

<div class="tab-content"  style="padding-top: 15px;">
   
   <div id="tabsearh" class="tab-pane fade in active"> 
        <div class="model-list" data-model="/pages/sales/Model/sic">
            <div class="input-group model-search">
                <input type="text" class="form-control" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button">
                        <span class="glyphicon glyphicon-search"></span>&nbsp;
                        <?=T('Search')?>
                    </button> 
                </span>
            </div>
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
                <div class="col-lg-10"><?=$f->label('sic_description')?></div>
                <div class="col-lg-2"><?=$f->input('year','number')?></div>
            </div>
            <div class="row" id="ranking">
            </div>
        </div>

    </div>
    

</div>
