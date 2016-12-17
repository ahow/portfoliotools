<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
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
