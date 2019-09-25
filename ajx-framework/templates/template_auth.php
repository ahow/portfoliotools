<?php
  $this->db =  $this->newMod('db');     // Database connection
  $this->user =  $this->newMod('auth'); // Authorization
  
  // After init modules, used for redirection
  if (method_exists($this->page,'afterInit')) $this->page->afterInit();

  // Menu modules
  $authMenu = $this->newMod('authMenu','user_menu.js');
  $dataMenu = $this->newMod('pMenu','data_menu.js');
  $endmarketsMenu = $this->newMod('pMenu','endmarkets_menu.js');
  $companiesMenu = $this->newMod('pMenu','companies_menu.js');
  $themesMenu = $this->newMod('pMenu','themes_menu.js');
  // $pfmeasuresMenu = $this->newMod('pMenu','pfmeasures_menu.js');
  $userMenu = $this->newMod('pMenu','user_menu.js');
  // $pRightMenu = $this->newMod('pMenu','main_r_data.js');

?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="<?php echo $this->description ?>">
    <meta name="author" content="<?php echo $this->author ?>">
    <link rel="icon" href="/favicon.ico">
    <title><?php echo T($this->title) ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="/bootstrap-3.3.6/assets/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/custom.css?v=1" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="/bootstrap-3.3.6/assets/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php $this->echoCSS() ?>
    <link rel="icon" 
    type="image/png" 
    href="/images/logo.png">
  </head>  
  <body>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">Home</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            
          <ul class="nav navbar-nav">
              
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Themes&nbsp;<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <li><a href="<?php echo mkURL('/~/themeA'); ?>">Theme A</a></li>
                     <li><a href="<?php echo mkURL('/~/themeB'); ?>">Theme B</a></li>
                     <li><a href="<?php echo mkURL('/~/themeC'); ?>">Theme C</a></li>
                     <li><a href="<?php echo mkURL('/~/themeD'); ?>">Theme D</a></li>
                  </ul>
              </li>
              
             <?php
              $cf = $this;
             if ($cf->inGroup('admin') || $cf->inGroup('editor') )
             {
             ?>
              
              <!--  Portfolio analysis -->
              <li><a class="nav-item" href="/~/theme-exposures">Portfolio<br>analysis</a></li>
              
                            
              <!--  Sector analysis -->
              <li><a class="nav-item w-todo" href="/~/sector-analysis">Sector<br>analysis</a></li>
              
              
              <!--  Screener -->
              <li><a class="nav-item w-todo" href="/~/screener">Screener</a></li>
              
              <!--  Theme comparison -->
              <!--
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" 
              aria-haspopup="true" aria-expanded="false">Theme<br>comparison&nbsp;<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <?php // $themesMenu->display() ?>
                  </ul>
              </li>
             -->
              
              <!--  Company details -->
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" 
              role="button" aria-haspopup="true" aria-expanded="false">Company<br>details&nbsp;<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <?php $companiesMenu->display() ?>
                  </ul>
              </li> 
          

              <!--  Industry analysis -->
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
               aria-haspopup="true" aria-expanded="false">Industry<br>analysis&nbsp;<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <?php $endmarketsMenu->display() ?>
                  </ul>
              </li>


              <!--  Edit companies -->
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
               aria-haspopup="true" aria-expanded="false">Edit<br>companies&nbsp;<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <?php $dataMenu->display() ?>
                  </ul>
              </li>
             <?php 
             }
             if ($cf->inGroup('admin') || $cf->inGroup('editor') || $cf->inGroup('user'))
             {
             ?>                

            
             <?php 
             }
             if ($cf->inGroup('admin') || $cf->inGroup('editor')  || $cf->inGroup('user'))
             {
             ?>   
              
              
              <?php 
             }
             //  $userMenu->display();
              ?>
          </ul>          
          <?php  
              $authMenu->display(); 
          ?>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">
       <div class="alert alert-danger w-alert-error" role="alert"> 
         <button type="button" class="close"><span aria-hidden="true">×</span></button> 
           <p class="w-alert-content"></p> 
        </div>
        <div class="alert alert-success w-alert-ok" role="alert"> 
          <p class="w-alert-content"></p> 
        </div>
      <?php $this->page->display(); ?>
      <?php $this->showErrors(); ?>
    </div> <!-- /container -->

    <!-- Footer -->
    <footer class="py-3 bg-dark">
      <div class="container">
        <p class="m-0 text-center text-white">Andy Howard</p>
      </div>
      <!-- /.container -->
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="/bootstrap-3.3.6/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/bootstrap-3.3.6/assets/ie10-viewport-bug-workaround.js"></script>
    <script src="/js/common.js"></script>
    <?php $this->echoCSS() ?>
    <?php $this->echoJS() ?>
  </body>
</html>
