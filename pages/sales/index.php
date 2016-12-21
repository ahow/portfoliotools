<div class="jumbotron">
<h1 class="uppertitle">Portfolio tools</h1>    
<?php
  $cf = $this->cfg;
  
  if ($cf->inGroup('admin'))
  {
?>
<h2>Data</h2>  
<p><a href="<?=mkURL('/sales/upload')?>"><?=T('DATA_UPLOADING')?></a></p>
<p><a href="<?=mkURL('/sales/portfolio-list')?>"><?=T('portfolio-list')?></a></p>
<p><a href="<?=mkURL('/sales/metric-list')?>"><?=T('metric-list')?></a></p>
<p><a href="<?=mkURL('/sales/data-export')?>"><?=T('data-export')?></a></p>
<?php
  }
  if ($cf->inGroup('admin') || $cf->inGroup('editor') )
  {
?>
<h2>End markets</h2>  
<p><a href="<?=mkURL('/sales/industry-groups')?>"><?=T('INDUSTRY_GROUPS')?></a></p>
<p><a href="<?=mkURL('/sales/sic')?>"><?=T('SIC')?></a></p>
<p><a href="<?=mkURL('/sales/companies')?>"><?=T('companies')?></a></p>
<p><a href="<?=mkURL('/sales/market-summary')?>"><?=T('market-summary')?></a></p>
<?php
  }
  if ($cf->inGroup('admin') || $cf->inGroup('editor')  || $cf->inGroup('user') )
  {
?>

<h2>Portfolio measures</h2>  
<!-- <p><a href="<?=mkURL('/sales/market-ranking')?>"><?=T('market-ranking')?></a></p> -->
<p><a href="<?=mkURL('/sales/theme-exposures')?>"><?=T('theme-exposures')?></a></p> 
<p><a href="<?=mkURL('/sales/portfolio-metrics')?>"><?=T('portfolio-metrics')?></a></p>
<?php
  }
?>
</div>
