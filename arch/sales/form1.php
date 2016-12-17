<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<div id="form1">
    <div class="row">
        <div class="col-lg-6"><?=$f->search('id','company')?></div>
        <div class="col-lg-4"><?=$f->input('isin')?></div>
        <div class="col-lg-2"><?=$f->input('year','number')?></div>
    </div>
    <div class="row">
        <div class="col-lg-6"><?=$f->input('sector')?></div>
        <div class="col-lg-4"><?=$f->input('region')?></div>
        <!-- <div class="col-lg-2"><?=$f->input('minsize','number')?></div> -->
    </div>
    <div class="row" id="formdata">
    </div>
</div>
<div id="search_company"></div>
