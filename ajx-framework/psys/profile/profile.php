<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class profile extends wPage
  {  function __construct($cfg, $path, $seg=null)
     {  $cfg->title = 'Profile';
        $this->cfg = $cfg;
        $this->cfg->addJs('/js','formvalidator.js');
        $this->cfg->addJs('/html.php/psys/profile','profile.js');        
        $this->seg = $seg;
     }
     
     function display()
     {  echo '<div id="profile"></div>';
     }
     
     function afterInit()
     {  $user = $this->cfg->user->user;
        if (empty($user)) header('Location: '.mkURL('/login?gotoURL=/'.implode('/',$this->seg)));        
     }
  }
?>
