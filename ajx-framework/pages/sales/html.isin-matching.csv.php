<?php
  include(SYS_PATH.'lib/mime.php');
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {   output_headers('ISIN-matching-'.date('Y-md-His').'.csv');
      $db = $this->db;
      if (isset($this->csv_delim)) 
        $delim = $this->csv_delim;
      else $delim=',';
      $h = array('ISIN','ALIAS');      
      $qr=$db->query('select isin, isin_alias from sales_isin_matching');
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h, $delim);
      while ($r=$qr->fetch(PDO::FETCH_NUM))
      { fputcsv($fp, $r, $delim);
      }
      fclose($fp);
  } else  header("HTTP/1.0 404 Not Found");
?>
