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
        <div class="col-lg-4"><?=$f->range('theme_range',-2,2,0.1)?></div>
        <div class="col-lg-3"><?=$f->modelSelect('region','/pages/sales/Model/regions')?></div>        
    </div>
    <div class="row">
        <?php 
           $list = 'sales:Total sales;'
           // .'top3:% top 3;top5:% top 5;'
           // .'stability:Stability;'
           .'roic:ROIC;pe:PE;evebitda:EVBIDTA;market_cap:Market capacity;'
           .'payout:Payout;reviewed:reviewed'
           /* y3sales:3yr Sales growth;y3ebit:3yr EBIT growth;'
           .'y3assets:3yr Asset growth;y3capex:3yr Capex growth;'
           .'grwsales:Sales growth;grwebit:EBIT growth;'
           .'grwassets:Asset growth;grwcapex:Capex growth;'
           .'ebit-by-assets:ROA;capex-by-assets:Capex intensity;'
           .'sales-by-assets:Asset turnover'; */;
         ?>        
        <div class="col-lg-4"><?=$f->listSelect('x-axis',$list)?></div>
        <div class="col-lg-4"><?=$f->listSelect('y-axis',$list)?></div>
        <div class="col-lg-4">
            <div class="btn-group">
                <!-- <button style="margin-top:25px" class="btn btn-primary b-print" disabled="true">Print</button> -->
                <button style="margin-top:25px" class="btn btn-default b-csv" disabled="true">Download .CSV</button>
                <button style="margin-top:25px" class="btn btn-primary b-vchart">View Chart</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="container" style="min-width: 600px; height: 600px; margin: 0 auto"></div>
    </div>
</div>

<div class="array-list">
    <table class="table table-striped selectable">
        <thead></thead>
        <tbody></tbody>
    </table>
    <div class="list-pager"></div>
</div>

<form name="fprint" method="POST" target="_blank" action="<?=mkURL('/html.php/pages/sales/chart.pdf')?>">
  <input type="hidden" name="type" value="industry" />
  <input type="hidden" name="svg" />
  <input type="hidden" name="region" />
  <input type="hidden" name="minsize" />
  <input type="hidden" name="title" />
</form>
<div id="search_sic"></div>
<div id="search_subsec"></div>
