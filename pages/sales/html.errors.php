<?php
  include('../lib/mime.php');  
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {  $a = explode('pages/sales/errors/', $this->nav);     
     
     if (count($a)==2)
     {  $fid = $a[1];
        $fn = '../uploads/errlog-'.$fid;
        if (file_exists($fn))
        {  output_headers('errlog-'.$fid.'.txt');        
           echo file_get_contents($fn);
        } else header("HTTP/1.0 404 Not Found");
     }
     
  } else  header("HTTP/1.0 404 Not Found");
?>
