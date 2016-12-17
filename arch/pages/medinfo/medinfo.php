<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class medinfo extends wPage
  {  function medinfo($cfg, $path, $seg=null)
     {  $this->cfg = $cfg;
        $cfg->title = 'Medinfo';
        $this->includePageLocales(__DIR__);
        $this->cpage = '';
        $this->seg = $seg;
        if (isset($seg[1])) 
        { $cpage = __DIR__."/{$seg[1]}.php";
          if (!file_exists($cpage)) header("HTTP/1.0 404 Not Found");
          else 
          { $this->cpage = $cpage;
            $cfg->title = T($seg[1]);
          }
        }
       $this->cfg->addJs('/js', 'formvalidator.2.js');
       $this->cfg->addJs('/js', 'models.js');
       if (isset($seg[1])) $this->cfg->addJs('/html.php/pages/medinfo',$seg[1].'.js');
     }
     
     function display()
     {  echo '<h1>'.T($this->cfg->title).'</h1>';
        $user = $this->cfg->user->user;
        $db = $this->cfg->db;
        // echo '<pre>'.print_r($user, true).'</pre>';
        if ($this->cpage!='') include($this->cpage);
     }
  }
?>
