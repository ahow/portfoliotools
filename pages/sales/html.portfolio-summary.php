<?php 
include('empty_page.php');
emptyHeader('Portfolio summary'); ?>
<div class="container">
<?php $a = explode('sales/portfolio-summary/', $this->nav); 
     if (count($a)>1)
     {
  ?>    

  
<div class="row w-portfolio-id" data-id="<?php echo $a[1]; ?>">
     <div class="col-lg-12">
         <table class="table">
             <tbody id="p-options">
             </tbody>
         </table>
     </div>
</div>
<div class="row">
     <div class="col-lg-8">         
         <h3 id="p-name"></h3> 
     </div>
     <div class="col-lg-4">
     </div>
</div>
<div class="row">
     <div class="col-lg-12">
         <p id="p-description"></p>
     </div>
</div>
<div class="row">
        <div class="col-lg-2">
              <h3 class="sum-title"></h3>
              <p class="sum-descr"></p>
        </div>    
        <div class="col-lg-10">
                <div id="ch-theme-exposures" style="min-width: 450px; height: 350px; margin: 0 auto"></div>
        </div>    
</div>
<div class="row">
        <div class="col-lg-2">
              <h3 class="sum-title"></h3>
              <p class="sum-descr"></p>
        </div>  
        <div class="col-lg-5">
                <div id="esg-analys" style="min-width: 450px; height: 350px; margin: 0 auto"></div>
        </div>    
        <div class="col-lg-5">
                <div id="met-analys" style="min-width: 450px; height: 350px; margin: 0 auto"></div>
        </div>    
</div>
<div class="row">
        <div class="col-lg-2">
              <h3 class="sum-title"></h3>
              <p class="sum-descr"></p>
        </div>  
        <div class="col-lg-3">
                <div id="ch-social" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>
        <div class="col-lg-4">
                <div id="ch-by-company" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>        
        <div class="col-lg-3">
                <div id="ch-by-stakeholder" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>  
</div>
<div class="row" id="stewardship">
        <div class="col-lg-2">
              <h3 class="sum-title"></h3>
              <p class="sum-descr"></p>
        </div>  
        <div class="col-lg-5">
                <div id="ch-bar" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>
        <div class="col-lg-5">
                <div id="ch-line" style="min-width: 210px; height: 350px; margin: 0 auto"></div>
        </div>
</div>

<?php
    }
?>


</div> <!-- /container -->
<?php emptyFooter(array('stacked.js','stacked-gradient.js',
'circles-chart.js','circles-best-worst.js','portfolio-summary-charts.js','portfolio-summary-html.js')); ?>
