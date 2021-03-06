<?php
// obsolete should be removed
include(SYS_PATH.'lib/mime.php');

if ($this->inGroup('admin') || $this->inGroup('editor'))
{ 
  output_headers('DivisionDetails-'.date('Y-md-His').'.csv');
  
  if (isset($this->csv_delim)) 
    $delim = $this->csv_delim;
  else $delim=',';
  
      $db = $this->db;
      $qr=$db->query('select min(syear) as minyear, max(syear) as maxyear from sales_divdetails');
      $yr = $db->fetchSingle($qr); 
      $h = array('Division','CID','');
      for ($i=$yr->maxyear; $i>=$yr->minyear; $i--) 
      { $h[] = "me $i";
        $h[] = "SIC $i";
        $h[] = "Sales $i";
        $h[] = "EBIT $i";
        $h[] = "Assets $i";
        $h[] = "Capex $i";
      }
      
      
      $qr=$db->query('select cid,division,syear,me,sic,sales,ebit,assets,capex from sales_divdetails order by 1,2,3 desc');
      

      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h, $delim);
      
      $last = 1;
      $row = array();
      $pr = array();
      
      while ($r=$qr->fetch(PDO::FETCH_OBJ))
      {   if ($last!=$r->division && !empty($row))
          {  $n = 3;
             for ($i=$yr->maxyear; $i>=$yr->minyear; $i--) 
             { if (isset($pr[$i])) 
               {  $row[$n] = $pr[$i][0];
                  $row[$n+1] = $pr[$i][1];
                  $row[$n+2] = $pr[$i][2];
                  $row[$n+3] = $pr[$i][3];
                  $row[$n+4] = $pr[$i][4];
                  $row[$n+5] = $pr[$i][5];
                  
               } else
               {  $row[$n] = '';
                  $row[$n+1] = '';
                  $row[$n+2] = '';
                  $row[$n+3] = '';
                  $row[$n+4] = '';
                  $row[$n+5] = '';
               }
               $n+=6;
             }
             fputcsv($fp, $row, $delim); 
             $last = $r->division;
             $row = array();
             $pr = array();             
          }
          $row[0] = $r->division;
          $row[1] = $r->cid;
          $row[2] = '';
          $pr[$r->syear][0] = $r->me;
          $pr[$r->syear][1] = $r->sic;
          $pr[$r->syear][2] = $r->sales;                    
          $pr[$r->syear][3] = $r->ebit;                    
          $pr[$r->syear][4] = $r->assets;                    
          $pr[$r->syear][5] = $r->capex;                    
      }

     // Last row
     $n = 3;
     for ($i=$yr->maxyear; $i>=$yr->minyear; $i--) 
     { if (isset($pr[$i])) 
       {  $row[$n] = $pr[$i][0];
          $row[$n+1] = $pr[$i][1];
          $row[$n+2] = $pr[$i][2];
          $row[$n+3] = $pr[$i][3];
          $row[$n+4] = $pr[$i][4];
          $row[$n+5] = $pr[$i][5];
          
       } else
       {  $row[$n] = '';
          $row[$n+1] = '';
          $row[$n+2] = '';
          $row[$n+3] = '';
          $row[$n+4] = '';
          $row[$n+5] = '';
       }
       $n+=6;
     }
     fputcsv($fp, $row,  $delim); 
      
     fclose($fp);
     
} else  header("HTTP/1.0 404 Not Found");

?>
