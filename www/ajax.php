<?php
   /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */  
  include('path.php'); 
  include(SYS_PATH.'ajerrors.php');
  include(SYS_PATH.'classes.php');
  
  class wAjax extends wMod
  {  var $res;
     var $seg;
     function __construct($cfg, $path,$seg=null)
     {  parent::__construct($cfg, $path, $seg);
        $this->seg = $seg;
        $this->res = new stdClass();
        $this->res->error = false;
        
        if (isset($seg[2]))
        {  $fu = 'ajx'.$seg[2];
           if (method_exists($this, $fu))
           { $this->$fu();
           } else $this->error('Method not found: '.$fu, 3003);
        }
     }

    function includePageLocales($dir)
    { global $_TRANSLATIONS;
       $trfile = $dir.'/'.$this->cfg->lang.'.ini';
       if (file_exists($trfile)) 
       { $TR = parse_ini_file($trfile);
         $_TRANSLATIONS = array_merge($_TRANSLATIONS, $TR);
       } else $this->error(T('TRANSLATION_FILE_NOT_FOUND')." $trfile", 44);
    }
        
     function error($msg, $no)
     { $this->res->error = true;
       $this->res->errmsg = $msg;
       $this->res->errno = $no;
       echo json_encode($this->res);
       return false;
     }
     
     function authGroup($group)
     { if ($this->cfg->inGroup($group)) return true;
       $this->error("Вы не авторизованы для группы $group", 3004);
       return false;
     }
  }
  
  class wMain extends wBase
  {  var $ajax;
     var $db;
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
        $this->nav = $p;
        $a = explode('/',$p);
        if (count($a)<3) 
        { echo '{"error":true,"errmsg":"Неверный адрес запроса '.$p.'","errno":3001}';
          return;
        }
        $type = $a[0];
        $path = $a[1];
        $mod = 'ajx'.$a[1];
                
        if ($p!='')
        { $trfile = SYS_PATH.$type.'/'.$path.'/ajx'.$this->lang.'.ini';
          if (file_exists($trfile)) $_TRANSLATIONS = parse_ini_file($trfile);
        
          $inc = SYS_PATH.$type.'/'.$path.'/'.$mod.'.php';
          if (file_exists($inc))
          { include($inc);
            $this->ajax = new $mod($this, $type.'/'.$path ,$a);            
          } else echo '{"error":true,"errmsg":"Файл '.$inc.' не найден!","errno":3002}';
          
        } 
     }
  }

  include(SYS_PATH.'config.php');
  $conf = new wConfig();
  date_default_timezone_set($conf->default_timezone);
  $conf->route();

?>
