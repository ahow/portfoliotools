<?php
  include(SYS_PATH.'lib/mime.php');  
  if ($this->inGroup('admin') || $this->inGroup('editor'))
  {   output_headers('CompanyList-'.date('Y-md-His').'.csv');
  
      if (isset($this->csv_delim)) 
        $delim = $this->csv_delim;
      else $delim=',';

      $db = $this->db;         
      // $h = array('CID','NAME','INDUSTRY GROUP','INDUSTRY','SECTOR','SUBSECTOR','COUNTRY','ISIN','REGION','SALES','MARKET CAP','Sales growth','ROIC','PE','EVEBITDA','Payout','REVIEWED');
      $h = array('CID','NAME','INDUSTRY GROUP','INDUSTRY',
      'SECTOR','SUBSECTOR','COUNTRY','ISIN','REGION',
      'Sales','Market cap','Sales growth','EBITDA growth','ROIC','ROE',
      'Price to earnings','EV to EBITDA','Yield','Price to book','Reinvestment','Research and development',
      'Net debt to EBITDA','CAPE','SustainEx','REVIEWED');
      $qr=$db->query('select c.cid, c.name, c.industry_group, c.industry, 
 c.sector, c.subsector, c.country, c.isin, c.region, 
 c.sales, c.market_cap, c.sales_growth, c.EBITDA_growth, c.roic, c.ROE,
 c.pe, c.evebitda, c.yield, c.price_to_book, c.reinvestment, c.research_and_development,
 c.net_debt_to_EBITDA, c.CAPE, c.sustain_ex, c.reviewed
 from sales_companies c');
      // c.payout obsolete
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h, $delim);
      while ($r=$qr->fetch(PDO::FETCH_NUM))
      { fputcsv($fp, $r, $delim);
      }
      fclose($fp);
  } else  header("HTTP/1.0 404 Not Found");
?>
