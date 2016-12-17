<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class about extends wPage
  {  function about($cfg, $path, $seg=null)
     {  $cfg->title = 'About';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
       $this->cfg->addJs('/html.php/pages/about','about.js');
     }
       
     function display()
     { 
       ?>
        <h1>Contact Information</h1>
        <div id="info" style="max-width:500px; padding-top: 20px; padding-bottom: 20px; "></div>
        <button class="btn btn-lg btn-success" id="view">View Contact Information</button>
        <?php
        
     }
  }
?>
