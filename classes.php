<?php
   /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */  
  function get($k, $def='')
  { if (isset($_GET[$k])) return $_GET[$k];
    return $def;
  }

  function post($k, $def='')
  { if (isset($_POST[$k])) return $_POST[$k];
    return $def;
  }
  
  function mkURL($url)
  {   global $conf;
      $pref = '/';
      if (isset($conf->root_prefix)) $pref = $conf->root_prefix;
      if ( ( isset($conf->sef) && $conf->sef) || 
           ( isset($_SERVER['MOD_REWRITE_SEF']) 
             && strtolower($_SERVER['MOD_REWRITE_SEF'])=='on') 
         ) return $pref.$url;
      return $pref.'/index.php'.$url;
  }
  
  function write_log($msg)
  {  $f = fopen(__DIR__.'/sys.log','a+');
     fwrite($f, date("Y-m-d H:i:s ").$msg."\n");
     fclose($f);
  }

  class wPage
  {  var $path = null;
     var $cfg = null;
    
     function wPage($cfg, $path,$seg=null)
     { if (!file_exists($path)) 
       {   header("HTTP/1.0 404 Not Found");
           $cfg->setError('Page not found '.$path, 404);
       }
       else 
       { $this->path = $path;
         $this->cfg = $cfg;
       }
       if (isset($seq)) $cfg->active=$seq[0];
     }
     function includePageLocales($dir)
     { global $_TRANSLATIONS;
       $trfile = $dir.'/'.$this->cfg->lang.'.ini';
       if (file_exists($trfile)) 
       { $TR = parse_ini_file($trfile);
         $_TRANSLATIONS = array_merge($_TRANSLATIONS, $TR);
       } else $this->cfg->setError(T('TRANSLATION_FILE_NOT_FOUND')." $trfile", 44);
     }
     function display()
     {  if ($this->path!=null) include($this->path);         
     }
  }
  
  class wMod
  { protected $cfg = null;
    var $path = null;
    var $data = null;

    function wMod($cfg, $path, $data='')
    { $this->cfg = $cfg;
      $this->path = $path;
      if ($data!='')
      { if (!is_array($data))
        {  $f = '../'.$path.'/'.$data;
           if  (!file_exists($f)) $cfg->setError('Module data not found '.$f, 404);
           else $this->data = json_decode( file_get_contents($f) );
        }
      }
    }
    
    function display()
    {
    }
  }
  
  class wBase
  {  var $errors = array();
     var $js = array();
     var $css = array();
     
     function setError($msg, $code=-1, $file='', $line=0 )
     {  $e = new stdClass;
        $e->message = $msg;
        $e->code = $code;
        $e->file = $file;
        $e->line = $line;
        $this->errors[] = $e;
     }
       
     function newMod($mod, $data='')
     {  try
        {   $inc = "../modules/$mod/$mod.php";
            if (file_exists($inc)) 
            { include_once($inc);
              return new $mod($this, "modules/$mod", $data);
            } else $this->setError('Module not found '.$mod, 404);
        } catch (Exception $e)
        {   $this->setError($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
        }
     }
     
     /*
     function addJS($path, $f)
     {  $p = implode('-',explode('/',$path.'/'.$f));
        if (file_exists("$path/$f") || substr($f,0,4)=='http') 
        {  $this->js[]="$path/$f";
           return;
        }
        $nf = "cache/$p";
        $sf = "../$path/$f";        
        if ((!file_exists($nf)) && file_exists($sf)) copy($sf, $nf);
        $this->js[]=$nf;
     }
     
     function addCSS($path, $f)
     {  $p = implode('-',explode('/',$path.'/'.$f));
        if (file_exists("$path/$f") || substr($f,0,4)=='http') 
        {  $this->css[]="$path/$f";
           return;
        }
        $nf = "cache/$p";
        $sf = "../$path/$f";        
        if ((!file_exists($nf)) && file_exists($sf)) copy($sf, $nf);
        $this->css[]=$nf;
     }
     */
     
     function addJS($path, $js){ $this->js[]="$path/$js"; }
     function addCSS($path, $css){ $this->css[]="$path/$css"; }
     
     function echoJS()
     { foreach($this->js as $j) echo '<script src="'.$j.'"></script>'."\n";
     }

     function echoCSS()
     { foreach($this->css as $c) echo '<link rel="stylesheet" type="text/css" href="'.$c.'">'."\n";
     }
          
     function inGroup($grp)
     { if ($this->user!=null && isset($this->user->user->groups[$grp])) return true;
       return false;
     }
     
     function getUID()
     {  if ($this->user!=null && isset($this->user->user->id)) return $this->user->user->id;
        return null;
     }
  }
  
  $_TRANSLATIONS = array();
  
  function T($text)
  {   global $_TRANSLATIONS;
      if (isset($_TRANSLATIONS[$text])) return $_TRANSLATIONS[$text];
      else return $text;
  }
  
  
?>
