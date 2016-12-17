<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class sales extends wPage
  {  function sales($cfg, $path, $seg=null)
     {  $this->cfg = $cfg;
        $this->includePageLocales(__DIR__);
        $cfg->title = 'Sales';
        $this->cpage = '';
        $this->seg = $seg;
        $this->nav ='';
        if (isset($seg[1])) 
        { $cpage = __DIR__."/{$seg[1]}.php";
          if (!file_exists($cpage)) header("HTTP/1.0 404 Not Found");
          else 
          { $this->cpage = $cpage;
            $this->nav = $seg[1];
            $cfg->title = T($this->nav);
          }
        } else $this->cpage=__DIR__."/index.php";
       $this->cfg->addJs('/js', 'formvalidator.2.js');
       $this->cfg->addJs('/js', 'models.js');
       if ($this->nav=='upload')
            $this->cfg->addJs('/html.php/pages/sales','upload.js');
       else
            $this->cfg->addJs('/html.php/pages/sales','inittable.js');
       if ($this->nav=='form1') 
            $this->cfg->addJs('/html.php/pages/sales','form1.js');
            
       //$this->cfg->addJs('/html.php/pages/sales',$seg[1].'.js');
     }
     
     function display()
     {  $user = $this->cfg->user->user;        
        if ($this->cpage!='') include($this->cpage);
     }
  }
?>
