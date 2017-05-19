<?php
  include('../lib/mime.php');  
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {   output_headers('SICDesc-'.date('Y-md-His').'.csv');
      $db = $this->db;
      
      $qr=$db->query('select headers from sales_exposure limit 1');
      $exph = $db->fetchSingleValue($qr);      
      
      $h = array('DIVISION','MAJOR GROUP','INDUSTRY GROUP CODE','INDUSTRY GROUP NAME','SIC CODE','SIC NAME','SIC DESCRIPTION');
      $h = array_merge($h, explode(';',$exph) );
      
      $qr=$db->query('select 
ig.division, ig.major_group,  ig.id, ig.industry_group, s.id, s.name, s.description, s.exposure 
 from sales_sic s
 join sales_industry_groups ig on s.industry_group_id=ig.id');
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h,',');
      while ($r=$qr->fetch(PDO::FETCH_NUM))
      {   $exp = $r[7];
          unset($r[7]);
          $r = array_merge($r, explode(';',$exp) );
          fputcsv($fp, $r, ',');
      }
      fclose($fp);
  } else  header("HTTP/1.0 404 Not Found");
?>
