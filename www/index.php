<?php
  // print_r($_SERVER);
  // die();
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  error_reporting(E_ALL);  
  include('path.php'); 
  include(SYS_PATH.'errors.php');
  include(SYS_PATH.'classes.php');

  
  class wMain extends wBase
  {  var $page = null;
     var $nav = '';
     
     function template()
     { include(SYS_PATH.$this->template);
     }
     
     function route()
     {  // read redirections
        $redir=json_decode( file_get_contents(SYS_PATH.'redirections.json') );
        if (json_last_error()!=JSON_ERROR_NONE)
        {  $this->setError(T('JSON_ERROR').' in  redirections.json: '.json_last_error_msg(),__LINE__);
           $redir=null;  
        }
               
        $p = '';
        if (isset($_SERVER['PATH_INFO']))  $p = substr($_SERVER['PATH_INFO'],1);
        else if (isset($_SERVER['REQUEST_URI']))  
        {   $a = explode('?', $_SERVER['REQUEST_URI']);
            $p = substr($a[0],1);
        }
        else if (isset($_SERVER['REDIRECT_URL']))  $p = substr($_SERVER['REDIRECT_URL'],1);
        if (strpos($p,'index.php')===0) $p=substr($p,10); // remove index.php if needed
        $this->nav = $p;
        $a = explode('/',$p);
        if ($p!='')
        { $pd = 'pages';
          $a0 = $a[0];
          if ($redir!=null && property_exists($redir,$a0))
          {   $r = $redir->$a0;
              $a0=$r->to;
              $pd=$r->p;
          }
          $pd.='/'.$a0;
          $inc = SYS_PATH.$pd.'/'.$a0.'.php';
          if (file_exists($inc))
          { include($inc);
            $this->page = new $a0($this, $pd, $a);
          } else 
          {  $index = SYS_PATH.$pd.'/index.php';
             $this->page = new wPage($this, $index ,$a);
          }
        } else
        $this->page = new wPage($this,SYS_PATH.'default.php');

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


  include(SYS_PATH.'config.php');

  $conf = new wConfig();
  $trfile = SYS_PATH."lang/$conf->lang.ini";
  if (file_exists($trfile)) $_TRANSLATIONS = parse_ini_file($trfile);
  date_default_timezone_set($conf->default_timezone);
  $conf->route();

?>
