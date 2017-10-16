<?php
  $pass = md5('admin');
  echo "insert into mc_users values (1,'admin','','Администратор','','','$pass');";
?>
