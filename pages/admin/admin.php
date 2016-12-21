<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class admin extends wPage
  {  function admin($cfg, $path, $seg=null)
     {  $cfg->title = 'ADMIN_PANEL';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->cfg->addJs('/js','formvalidator.js');
        $this->cfg->addJs('/js','models.js');
        $this->cfg->addJs('/html.php/pages/admin','admin.js');
     }
     
     function display()
     { if ( $this->cfg->inGroup('admin') )
       {   parent::display();
       } else $this->cfg->setError(T("AUTH_REQURED"), 1029);
     }
  }
?>
