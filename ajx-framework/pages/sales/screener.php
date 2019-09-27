<?php
    $this->displayCrumbs();
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
    $f->labeled = false;
?>
<div id="screener">
    <div class="row">
        <div class="col-lg-6">
 <?php
    $db = $this->cfg->db;
    $qr = $db->query('select * from sales_theams order by id');   
    $i=0;
    while ($r = $db->fetchSingle($qr))
    {   
 ?>
    <form class="form-inline w-h-controls w-themes row" data-id="<?=$r->id?>">
        <div class="form-group">
            <label><?=$r->theam?></label>            
        </div>
        <div class="form-group w-slider">
            <b class="w-lbl-left">-10</b>
            <input id="theme<?=($i++)?>" type="text" class="span2 bs-range" value="" data-control-type="range" data-slider-min="-10" data-slider-max="10" data-slider-step="1" data-slider-value="[-10,10]">
            <b class="w-lbl-right">10</b>
        </div>        
        <div class="form-group">
            <input type="number" class="form-control w-short" data-control-type="basic"  placeholder="Weight">
        </div>        
    </form>

  <?php
    }

    $item_list = ['', 'market_cap','sales_growth','EBITDA_growth','ROIC','ROE',
    'price_to_earnings','ev_to_ebitda', 'yield', 'price_to_book', 'reinvestment',
    'research_and_development', 'net_debt_to_EBITDA', 'CAPE', 'sustain_ex'];

    $selector = '<select class="form-control">';
    foreach($item_list as $v) $selector.='<option id="'.$v.'">'.T($v).'</option>';
    $selector .= '</select>';
?>  
        </div>
        <div class="col-lg-6">

<?php
    for ($i=1; $i<=5; $i++)
    {
?>
    <form class="form-inline w-h-controls row">
    <div class="form-group">
        <?=$selector?>          
    </div>
    <div class="form-group w-slider">
        <b class="w-lbl-left">-10</b>
        <input id="range<?=($i)?>" type="text" class="span2 bs-range" value="" data-control-type="range" data-slider-min="-10" data-slider-max="10" data-slider-step="1" data-slider-value="[-10,10]">
        <b class="w-lbl-right">10</b>
    </div>        
    </form>
<?php
    }
?>
        </div>
    </div>

    <div class="model-list" data-model="/pages/sales/Model/companies">
        <table class="table table-striped selectable">
            <thead></thead>
            <tbody></tbody>
        </table>
        <div class="model-pager"></div>
    </div>
    
</div>
