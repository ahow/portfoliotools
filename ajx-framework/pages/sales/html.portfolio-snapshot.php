<?php 
include('empty_page.php');
emptyHeader('Portfolio snapshot'); ?>
<div class="container">
  
  <div class="alert alert-danger w-alert-error" role="alert"> 
      <button type="button" class="close"><span aria-hidden="true">×</span></button> 
      <p class="w-alert-content"></p> 
  </div>
  <div class="alert alert-success w-alert-ok" role="alert"> 
       <p class="w-alert-content"></p> 
  </div>
        

  <?php $a = explode('sales/portfolio-snapshot/', $this->nav); 
     if (count($a)>1)
     {
  ?>    
  <div>
        <div class="row">
            <div class="col-lg-10">
                <div data-id="<?php echo $a[1]; ?>" id="chart" style="min-width: 450px; height: 350px; margin: 0 auto"></div>
            </div>
            <div class="col-lg-2">
            </div>
        </div>
  </div>
  <?php
    }
  ?>
</div> <!-- /container -->
<?php emptyFooter(array('portfolio-snapshot.js', 'portfolio-snapshot-html.js')); ?>
