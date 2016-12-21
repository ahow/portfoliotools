<?php
  include('../lib/mime.php');
  $id = get('id');
  
if ($this->inGroup('admin') || $this->inGroup('editor'))
{ 
  output_headers('Metric-'.$id.'.csv');
  //if ($this->inGroup('admin'))
  //{  
      $db = $this->db;
      $h = array('ISIN');
      $qr=$db->query('select name from sales_metrics_columns where metric_id=:id order by col', array('id'=>$id) );
      $n=0;
      while ($h[] = trim($db->fetchSingleValue($qr))) $n++; // Get column headers name
      
      if ($n==1)
      {   $qr=$db->query('select isin,val from sales_metrics_data where metric_id=:id', array('id'=>$id) ); 
          $fp = fopen('php://output', 'w');
          fputcsv($fp, $h,',');
          while ($r=$qr->fetch(PDO::FETCH_NUM))
          { fputcsv($fp, $r, ',');
          }
          fclose($fp);
       } else
       {  $isin = '';
          $first = true;
          $qr=$db->query('select isin,val,col from sales_metrics_data where metric_id=:id order by isin,col', array('id'=>$id) ); 
          $fp = fopen('php://output', 'w');
          fputcsv($fp, $h,',');
          $a = array();
          while ($r=$qr->fetch(PDO::FETCH_OBJ))
          {   if ($first)
              {  $a = array();
                 $a[0] = $r->isin;
                 $a[1] = $r->val;
                 $isin = $r->isin;
                 $first = false;
              } else
              if ($isin!=$r->isin)
              {  fputcsv($fp, $a, ',');
                 $isin = $r->isin;
                 $a = array();
                 $a[0] = $r->isin;
                 $a[1] = $r->val;
              } else $a[] = $r->val;
          }
          fputcsv($fp, $a, ',');
          fclose($fp);
       }
} else  header("HTTP/1.0 404 Not Found");

?>
