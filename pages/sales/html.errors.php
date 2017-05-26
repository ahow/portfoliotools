<?php
  include('../lib/mime.php');  
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {  $a = explode('pages/sales/errors/', $this->nav);     
     
     if (count($a)==2)
     {  $fid = $a[1];
        output_headers('errlog-'.$fid.'.txt');
        echo file_get_contents('../uploads/errlog-'.$fid);
     }
     
  } else  header("HTTP/1.0 404 Not Found");
?>
