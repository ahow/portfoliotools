<?php
    $this->displayCrumbs();
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
        <div class="col-lg-5"><?=$f->modelSelect('comparison','/pages/sales/Model/portfolio-lookup')?></div>
        <div class="col-lg-2">
            <div class="btn-group">
                <button style="margin-top:25px" class="btn btn-primary b-print" disabled="true">Print</button>
                <button style="margin-top:25px" class="btn btn-primary b-vchart">View Chart</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12"><?=$f->textarea('description')?></div>
    </div>
    <div class="row" id="ranking">
        <div class="col-lg-6">
                <!-- 
                <canvas id="chart" width="1000" height="400">
                </canvas>
                -->
                <div id="container" style="min-width: 210px; height: 400px; margin: 0 auto"></div>
        </div>
        <div class="col-lg-6">
                <div id="container2" style="min-width: 210px; height: 400px; margin: 0 auto"></div>
        </div>        
    </div>
    <div class="row" id="ranking">
        <div class="col-lg-12">
                <div id="stacked" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
        </div>
    </div>

<form name="fprint" method="POST" target="_blank" action="<?=mkURL('/html.php/pages/sales/chart.pdf')?>">
  <input type="hidden" name="type" value="pfmetrics" />
  <input type="hidden" name="svg1" />
  <input type="hidden" name="svg2" />
  <input type="hidden" name="svg3" />
  <input type="hidden" name="title" />
</form>


</div>
