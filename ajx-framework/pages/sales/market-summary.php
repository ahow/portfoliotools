<?php
    $this->displayCrumbs();
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>
<div id="mranking">
    <div class="row">
        <div class="col-lg-7"><?=$f->search3dot('sic_code','sic')?></div>
        <div class="col-lg-5"><?=$f->modelSelect('region','/pages/sales/Model/regions')?></div>
        <!-- <div class="col-lg-2"><?=$f->input('minsize','number')?></div> -->
    </div>
    <div class="row">
        <div class="col-lg-6"><?=$f->search3dot('subsec','subsector')?></div>
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

        // $list = 'Total sales;% top 3;% top 5;Stability;Sales growth;ROIC;PE;EVBIDTA;3yr Sales growth;EBIT growth;3yr EBIT growth;EBIT margin;ROA;Asset growth;3yr Asset growth;Asset turnover;Capex growth;3yr Capex growth;Capex intensity'; 
        $list = 'tsales:Total sales;top3:% top 3;top5:% top 5;stability:Stability;'
        .'roic:ROIC;pe:PE;evebitda:EVBIDTA;payout:Payout;y3sales:3yr Sales growth;'
        .'y3ebit:3yr EBIT growth;y3assets:3yr Asset growth;y3capex:3yr Capex growth;'
        .'grwsales:Sales growth;grwebit:EBIT growth;grwassets:Asset growth;'
        .'grwcapex:Capex growth;ebit-by-assets:ROA;capex-by-assets:Capex intensity;'
        .'sales-by-assets:Asset turnover;y5sales:5yr Sales growth;'
        .'y5ebit:5yr EBIT growth;y5capex:5yr Capex growth';
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
        
    <div class="row" id="debug" style="display:none">
    </div>
    
</div>

<div class="array-list">
    <table class="table table-striped selectable">
        <thead></thead>
        <tbody></tbody>
    </table>
    <div class="list-pager"></div>
</div>

<div id="search_sic"></div>
<div id="search_subsec"></div>
