<?php
  include('path.php');
  include(SYS_PATH.'errors.php');
  include(SYS_PATH.'classes.php');
  
  class wMain extends wBase
  {  var $res;
     var $seg;
     var $db;
     var $cfg;
     
     function authGroup($group)
     { if ($this->cfg->inGroup($group)) return true;
       $this->error(T('ERR_NOT_AUTHORIZED_FOR_GROUP').": $group", 3004);
       return false;
     }
     
     function route()
     {  global $_TRANSLATIONS;
        $this->db = $this->newMod('db');
        $this->user = $this->newMod('auth');
     
        $p = '';
        if (isset($_SERVER['PATH_INFO']))  $p = substr($_SERVER['PATH_INFO'],1);
        else if (isset($_SERVER['REQUEST_URI']))  
        {   $a = explode('?', $_SERVER['REQUEST_URI']);
            $p = substr($a[0],10);            
        }
        if (strpos($p,'html.php')===0) $p=substr($p,9);
        $this->nav = $p;
        $a = explode('/',$p);
        if (count($a)<3) 
        { $this->setError(T('BAD_REQUEST').' '.$p);
          return;
        }
        $type = $a[0];
        $path = $a[1];
        $mod =  $a[2];
        
        if ($p!='')
        { $trfile = SYS_PATH.$type.'/'.$path.'/'.$this->lang.'.ini';
          if (file_exists($trfile)) $_TRANSLATIONS = parse_ini_file($trfile);
          if (substr($mod,-3)=='.js')
          {  $inc = SYS_PATH.$type.'/'.$path.'/'.$mod;
             header("Content-Type:application/javascript");
          } else
          if (substr($mod,-4)=='.css')
          {  $inc = SYS_PATH.$type.'/'.$path.'/'.$mod;
             header("Content-Type:text/css");
          }          
          else
            $inc = SYS_PATH.$type.'/'.$path.'/html.'.$mod.'.php';
            
          if (file_exists($inc))
          { include($inc);            
          } else 
          {  header("HTTP/1.0 404 Not Found");
             echo $inc;
              // $this->setError(T('FILE_NOT_FOUND').' ('.$inc.')');
          }
        } 
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
  date_default_timezone_set($conf->default_timezone);
  $conf->route();
  $conf->showErrors();
?>
