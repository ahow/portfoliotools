<?php
    $cfg = json_decode(file_get_contents(__DIR__.'/config.js'));
    foreach ($cfg as $r)
    { if ($r->enabled)
      { $servicesCredentials[$r->name] = array('key'=>$r->key,'secret'=>$r->secret);
      }
    }
?>
