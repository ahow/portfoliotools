<?php

include('../lib/mime.php');
  
if ($this->inGroup('admin') || $this->inGroup('editor'))
{ 
  output_headers('DivisionDetails-'.date('Y-md-His').'.csv');
  //if ($this->inGroup('admin'))
  //{  
      $db = $this->db;
      $qr=$db->query('select min(syear) as minyear, max(syear) as maxyear from sales_divdetails');
      $yr = $db->fetchSingle($qr); 
      $h = array('Division','CID','');
      for ($i=$yr->maxyear; $i>=$yr->minyear; $i--) 
      { $h[] = "me $i";
        $h[] = "SIC $i";
        $h[] = "Sales $i";
      }
      
      
      $qr=$db->query('select cid,division,syear,me,sic,sales from sales_divdetails order by 1,2,3 desc');
      
 
      $fp = fopen('php://output', 'w');
      fputcsv($fp, $h,',');
      
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
                  
               } else
               {  $row[$n] = '';
                  $row[$n+1] = '';
                  $row[$n+2] = '';
               }
               $n+=3;
             }
             fputcsv($fp, $row, ','); 
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
      }

     // Last row
     $n = 3;
     for ($i=$yr->maxyear; $i>=$yr->minyear; $i--) 
     { if (isset($pr[$i])) 
       {  $row[$n] = $pr[$i][0];
          $row[$n+1] = $pr[$i][1];
          $row[$n+2] = $pr[$i][2];
          
       } else
       {  $row[$n] = '';
          $row[$n+1] = '';
          $row[$n+2] = '';
       }
       $n+=3;
     }
     fputcsv($fp, $row, ','); 
      
     fclose($fp);
     
} else  header("HTTP/1.0 404 Not Found");

?>
