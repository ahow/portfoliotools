<?php
  include('../lib/mime.php');  
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {   output_headers('CompanyList-'.date('Y-md-His').'.csv');
      $db = $this->db;      
      $h = array('CID','NAME','INDUSTRY GROUP','INDUSTRY','SECTOR','SUBSECTOR','COUNTRY','ISIN','REGION','SALES','MARKET CAP','Sales growth','ROIC','PE','EVEBITDA','Payout','REVIEWED');
      $qr=$db->query('select c.cid, c.name, c.industry_group, c.industry, 
 c.sector, c.subsector, c.country, c.isin, c.region, c.sales, c.market_cap, c.sales_growth, c.roic, c.pe, c.evebitda, c.payout, c.reviewed
 from sales_companies c');
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h,',');
      while ($r=$qr->fetch(PDO::FETCH_NUM))
      { fputcsv($fp, $r, ',');
      }
      fclose($fp);
  } else  header("HTTP/1.0 404 Not Found");
?>
