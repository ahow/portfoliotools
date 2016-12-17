<?php
  include('../lib/mime.php');
  $id = get('id');
  output_headers('Portfolio-'.$id.'.csv');
  //if ($this->inGroup('admin'))
  //{  
      $db = $this->db;
      $h = array('ISIN');
      $qr=$db->query('select portfolio from sales_portfolio where id=:id', array('id'=>$id) );
      $h[] = trim($db->fetchSingleValue($qr));
 
      $qr=$db->query('select isin,val from sales_portfolio_data where portfolio_id=:id', array('id'=>$id) ); 
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h,',');
      while ($r=$qr->fetch(PDO::FETCH_NUM))
      { fputcsv($fp, $r, ',');
      }
      fclose($fp);
   //} else echo 'Access denied. Please login.';
?>
