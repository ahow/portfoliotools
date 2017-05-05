<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Portfolio tools">
    <meta name="author" content="Andrew Howard, Vitaliy Fedotov">
    <link rel="icon" href="/favicon.ico">
    <title>Portfolio Snapshot</title>
    <!-- Bootstrap core CSS -->
    <link href="/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="/bootstrap-3.3.6/assets/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/custom.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="/bootstrap-3.3.6/assets/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
      </head>  
  <body>
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


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="/bootstrap-3.3.6/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/bootstrap-3.3.6/assets/ie10-viewport-bug-workaround.js"></script>
    <script src="/js/common.js"></script>
    <script src="/js/formvalidator.js"></script>
    <script src="/js/models.js"></script>
    <script src="/js/loadingoverlay.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="/html.php/pages/sales/portfolio-snapshot.js"></script>
    <script src="/html.php/pages/sales/portfolio-snapshot-html.js"></script>
  </body>
</html>

