<?php
  $this->db =  $this->newMod('db');     // Database connection
  $this->user =  $this->newMod('auth'); // Authorization
  
  // After init modules
  if (method_exists($this->page,'afterInit')) $this->page->afterInit();

  
  if (isset($this->user->user))
  {  $authMenu = $this->newMod('authMenu','user_menu.js');
     $pMenu = $this->newMod('pMenu','main_data.js');
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
    <link href="/css/custom.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="/bootstrap-3.3.6/assets/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php $this->echoCSS() ?>
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
          <a class="navbar-brand" href="/"><?php echo $this->description ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            
          <ul class="nav navbar-nav">
              <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Forms demo <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                     <?php $pMenu->display() ?>
                  </ul>
              </li>
              <?php $userMenu->display() ?>
          </ul>          
          <?php  $authMenu->display(); ?>
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
<?php 
  } else include('../templates/blank.php');
?>
