<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>
<div id="theme_exp">
    <div class="row">
        <div class="col-lg-5"><?=$f->modelSelect('metric','/pages/sales/Model/metric-lookup')?></div>
    </div>
    <div class="row">
        <div class="col-lg-5"><?=$f->modelSelect('portfolio','/pages/sales/Model/portfolio-lookup')?></div>
        <div class="col-lg-5"><?=$f->modelSelect('comparasion','/pages/sales/Model/portfolio-lookup')?></div>
        <div class="col-lg-2"><button style="margin-top:25px" class="btn btn-primary b-vchart">View Chart</button></div>
    </div>
    <div class="row">
        <div class="col-lg-12"><?=$f->textarea('description')?></div>
    </div>
    <div class="row" id="ranking">
        <div class="col-lg-12">
                <!-- 
                <canvas id="chart" width="1000" height="400">
                </canvas>
                -->
                <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
        </div>
        
    </div>
    <div class="row" id="ranking">
        <div class="col-lg-12">
                <div id="stacked" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
        </div>
        
    </div>


</div>