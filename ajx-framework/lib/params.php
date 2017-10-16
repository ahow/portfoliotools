<?php
  function getParams($db, $name='email')
  {  $qr = $db->query("select json from settings where name='email'");
     $params = json_decode($db->fetchSingleValue($qr));
     return $params;
  }
?>
