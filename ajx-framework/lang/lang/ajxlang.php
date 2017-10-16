<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include('../lib/params.php');
 // include('../lib/phpmailer.php');
 
 class ajxlang extends wAjax
 {  
    function ajxLocale()
    {  global $_TRANSLATIONS;
       $path = post('path');
       $this->includePageLocales(SYS_PATH.$path);
       if (!$this->res->error)
       {   $this->res->locale = $_TRANSLATIONS;
           echo json_encode($this->res);
       }
    }
 }
?>
