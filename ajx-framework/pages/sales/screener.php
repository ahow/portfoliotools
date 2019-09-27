<?php
    $this->displayCrumbs();
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
    $f->labeled = false;
?>
<div id="screener">
 <?php
    $db = $this->cfg->db;
    $qr = $db->query('select * from sales_theams order by id');   
    $i=0;
    while ($r = $db->fetchSingle($qr))
    {   
 ?>
    <form class="form-inline w-themes row" data-id="<?=$r->id?>">
        <div class="form-group">
            <label><?=$r->theam?></label>            
        </div>
        <div class="form-group w-slider">
            <b style="padding-right: 10px;">-10</b>
            <input id="theme<?=($i++)?>" type="text" class="span2 bs-range" value="" data-control-type="range" data-slider-min="-10" data-slider-max="10" data-slider-step="1" data-slider-value="[-10,10]">
            <b style="padding-left: 10px;">10</b>
        </div>        
        <div class="form-group">
            <input type="number" class="form-control" data-control-type="basic"  placeholder="Weight">
        </div>        
    </form>

  <?php
    }
  ?>
   <div class="row" id="ranking"></div>

</div>
