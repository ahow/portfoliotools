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
        <div class="col-lg-4"></div>
        <div class="col-lg-2"><button style="margin-top:25px" class="btn btn-primary b-vchart">View Chart</button></div>
    </div>
    <div class="row">
        <?php $list = 'Total sales;Sales growth;ROIC;PE;EVBIDTA;Payout;% reviewed'; ?>
        <div class="col-lg-6"><?=$f->listSelect('x-axis',$list)?></div>
        <div class="col-lg-6"><?=$f->listSelect('y-axis',$list)?></div>
    </div>
    <div class="row">
        <div id="container" style="min-width: 210px; height: 400px; margin: 0 auto"></div>
    </div>
</div>
<div id="search_sic"></div>
<div id="search_subsec"></div>
