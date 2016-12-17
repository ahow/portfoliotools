<?php
  // print_r($_SERVER);
  // die();
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  error_reporting(E_ALL);  
  include('../errors.php');
  include('../classes.php');
  
  
  class wMain extends wBase
  {  var $page = null;
     var $nav = '';
     
     function template()
     { include('../'.$this->template);
     }
     
     function route()
     {  $p = '';
        if (isset($_SERVER['PATH_INFO']))  $p = substr($_SERVER['PATH_INFO'],1); else
        if (isset($_SERVER['REDIRECT_URL']))  $p = substr($_SERVER['REDIRECT_URL'],1); else
        if (isset($_SERVER['REQUEST_URI']))  
        {   $a = explode('?', $_SERVER['REQUEST_URI']);
            $p = substr($a[0],1);
        }
        $this->nav = $p;
        $a = explode('/',$p);
        if ($p!='')
        { $inc = '../pages/'.$a[0].'/'.$a[0].'.php';
          $index = '../pages/'.$a[0].'/index.php';
          if (file_exists($inc))
          { include($inc);
            $this->page = new $a[0]($this, 'pages/'.$a[0] ,$a);
          } else $this->page = new wPage($this, $index ,$a);
        } else
        $this->page = new wPage($this,'../default.php');

        $this->template();
     }
     
     function showErrors()
     {   global $gl_errors;
         $a = array_merge($gl_errors, $this->errors);
         if (count($a)>0)
         { echo '<div class="alert alert-danger" role="alert">';
           foreach($a as $e) 
           {   if (is_object($e)) echo $e->message.'<br />';
               else echo $e.'<br />';
           }
           echo '</div>';
         }
     }
     
  }


  include('../config.php');

  $conf = new wConfig();
  $trfile = "../lang/$conf->lang.ini";
  if (file_exists($trfile)) $_TRANSLATIONS = parse_ini_file($trfile);
  $conf->route();

?>
