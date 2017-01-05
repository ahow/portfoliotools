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
        <div class="col-lg-6"><?=$f->search3dot('sic_code','sic')?></div>
        <div class="col-lg-4"><?=$f->modelSelect('region','/pages/sales/Model/regions')?></div>
        <div class="col-lg-2"><?=$f->input('minsize','number')?></div>
    </div>
    <div class="row">
        <div class="col-lg-6"><?=$f->search3dot('subsec','subsector')?></div>
    </div>
    <div class="row">
        <?php $list = 'Total sales;% top 3;% top 5;Stability;Sales growth;ROIC;PE;EVBIDTA;Payout;% reviewed'; ?>
        <div class="col-lg-6"><?=$f->listSelect('x-axis',$list)?></div>
        <div class="col-lg-6"><?=$f->listSelect('y-axis',$list)?></div>
    </div>
    <div class="row" id="summary">
    </div>
</div>
<div id="search_sic"></div>
<div id="search_subsec"></div>
