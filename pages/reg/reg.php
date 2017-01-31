<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class reg extends wPage
  {  function __construct($cfg, $path, $seg=null)
     {  $cfg->title = 'Регистрация участников';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->cfg->addJs('/js','formvalidator.js');
        $this->cfg->addJs('/html.php/pages/reg','reg.js');
     }
  }
?>
