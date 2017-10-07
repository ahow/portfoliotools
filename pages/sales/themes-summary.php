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
        <div class="col-lg-4"><?=$f->modelSelect('themes','/pages/sales/ModelThemes','')?></div>
        <div class="col-lg-3"><?=$f->range('theme_range',-2,2)?></div>
        <div class="col-lg-3"><?=$f->modelSelect('region','/pages/sales/Model/regions')?></div>
        <div class="col-lg-2"><button style="margin-top:25px" class="btn btn-primary b-summary">View summary</button></div>
    </div>
    
    <div class="row" id="summary">
    </div>
    
    <div class="row p-chart" style="display:none">
        <?php 
/*        
        Sales growth (year over year change in Sales)
3yr Sales growth
EBIT growth (year over year change in EBIT)
3yr EBIT growth
EBIT margin (EBIT divided by Sales)
ROA (EBIT divided by assets)
Asset growth (year over year change in capex)
3yr Asset growth
Asset turnover (sales to assets ratio)
Capex growth (year over year change in capex)
3yr Capex growth
Capex intensity (capex/assets)*/

        //$list = 'tsales:Total sales;% top 3;% top 5;stability:Stability;Sales growth;ROIC;PE;EVBIDTA;3yr Sales growth;EBIT growth;3yr EBIT growth;EBIT margin;ROA;Asset growth;3yr Asset growth;Asset turnover;Capex growth;3yr Capex growth;Capex intensity'; 
        $list = 'tsales:Total sales;top3:% top 3;top5:% top 5;stability:Stability;sales_growth:Sales growth;roic:ROIC;pe:PE;evebitda:EVBIDTA;';
        ?>
        <div class="col-lg-4"><?=$f->listSelect('LHS',$list)?></div>
        <div class="col-lg-4"><?=$f->listSelect('RHS',$list)?></div>
        <div class="col-lg-4">
            <div class="btn-group">
                <button style="margin-top:25px" class="btn btn-primary b-vchart">View Chart</button>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
             <div id="chart" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4"></div>
        <div class="col-lg-4"></div>
        <div class="col-lg-4"><button style="margin-top:25px" class="btn b-debug pull-right">Debug info ...</button></div>
    </div>
        
    <div class="row" id="debug" style="display:none">
    </div>
    
</div>
<div id="search_sic"></div>
<div id="search_subsec"></div>
