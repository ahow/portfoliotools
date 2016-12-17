<?php
  class setup extends wPage
  {  function setup($cfg, $path, $seg=null)
     {  $cfg->title = 'DATABASE_SETUP';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
     }
  }
?>
