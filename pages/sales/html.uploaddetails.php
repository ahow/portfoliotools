<?php
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache'); 
    
    $msg_num = 0;
    
    function send_message($id, $d) 
    {   echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
    }

     
     $res = new stdClass();
     $res->error = false;
     $res->proc = 0;

     $clear = get('clear',false);

     if (isset($_GET['tmp']))
     {  $tmp = $_GET['tmp'];
     } else
     {  $res->errmsg = 'File name missed in ?tmp= !';
        send_message('ERROR', $res);
        die();
     }

     if (!file_exists($tmp))
     {  $res->errmsg = "File $tmp is not exists!";
        send_message('ERROR', $res);
        die();
     }
     
     $f = fopen($tmp,'r');
     $total_lines = 0;
     while(fgets($f)) $total_lines++;
     fclose($f);
     
     $res->total = $total_lines;
     send_message(++$msg_num, $res);
     unset($res->total);
    
     $f = fopen($tmp,'r');
     $h = fgets($f);
     $a = explode(';',$h);
     $spl='';
     if (count($a)>1) $spl=';'; 
     else
     {  $a = explode(',',$h);
        if (count($a)>0) $spl=',';
     }

     $res->stage = 'file opened';
     send_message(++$msg_num, $res);
    
     $ncol = count($a);
     // Support for old and new format
     // if ( $ncol<7 || ( (($ncol-4) % 3)!=0 && (($ncol-3) % 3)!=0) )
     if ( $ncol<9 || (($ncol-3) % 6)!=0 )
     {  fclose($f);
        unlink($tmp);
        $res->errmsg = 'Wrong Division details format! ('."$ncol)";
        send_message('ERROR', $res);
        die();
     }
     
     $db = $this->db;
     
     $qr = $db->query('select count(*) from sales_sic');
     if ($db->fetchSingleValue($qr)==0)
     {  $res->errmsg = "SIC Desc is empty! Upload it first!";
        send_message('ERROR', $res);
        die();
     }

     $res->stage = 'db set';
     send_message(++$msg_num, $res);
     
     // Remember fo the years values
     $years = array();
     
     for ($i=3; $i<$ncol; $i+=3)
     {  $years[$i]= filter_var($a[$i], FILTER_SANITIZE_NUMBER_INT);
     }
     
     
     $cid = '';
     
     $lines = 0;
     $uploaded = 0;
     
      if ($clear) 
      {  $db->query('delete from sales_divdetails');
      }

     
     $res->stage = 'Import started';
     send_message(++$msg_num, $res);
     $fid = date('YmdHis').'.'.rand(1,10000);
     $fn = '../uploads/errlog-'.$fid;
     $fe = fopen($fn, 'w+');
     $res->errors = 0;
     
     while ($a = fgetcsv($f,0,$spl) )
     {  $division = trim( $a[0] );
        $cid = trim( $a[1] );
        $lines++;
        
        if (($lines % 100) == 0)
        {  $res->proc = number_format(($lines/$total_lines)*100, 2, '.', '');
           send_message(++$msg_num, $res);
        }
        
        for ($i=3; $i<$ncol; $i+=6)
        {   $r = new stdClass();
           //  $r->div_sale_id = $sale_id;
            $r->division = $division;
            $r->cid = $cid;
            $r->syear = $years[$i];
            $r->me = trim( $a[$i] );
            if ($r->me!='' && isset($a[$i+1]))
            {
                $sic = $a[$i+1];
                $r->sic = str_replace(',','.', trim( $sic ) );
                if ($r->sic=='') $r->sic=-1; 
                else $r->sic=round($r->sic);
                
                $r->sales =str_replace(',','.', trim( $a[$i+2] ) );                
                $r->ebit =str_replace(',','.', trim( $a[$i+3] ) );
                if ($r->ebit=='') $r->ebit=NULL;
                $r->assets =str_replace(',','.', trim( $a[$i+4] ) );
                if ($r->assets=='') $r->assets=NULL;
                $r->capex =str_replace(',','.', trim( $a[$i+5] ) );
                if ($r->capex=='') $r->capex=NULL;
                
                if ($r->sales=='') $r->sales=NULL;
                try
                { 
                    $db->insertObject('sales_divdetails',$r);
                    $uploaded++;
                    
                } catch(Exception $e)
                {    $d = new stdClass();
                     $d->message = $e->getMessage();
                     $err = 'Unknown';
                     if (strpos($d->message, 'FOREIGN KEY (`sic`)')!==false) $err = 'SIC not found'; else
                     if (strpos($d->message, 'Duplicate entry')!==false) $err = 'Duplicate entry';
                     $d->code = $e->getCode();
                     $d->line = $lines;
                     $d->row = $r;
                     
                     if ($d->code==23000 && ($clear || $err=='SIC not found'))
                     {  fwrite($fe, "Error: $err, Line: $lines, CID: $cid, year: $r->syear, me: $r->me, sic: $sic\n");
                        $res->errors++;
                        send_message('LINE_ERR', $d);
                     } else
                     if ($err=='Unknown')
                     {  $res->errors++;
                        fwrite($fe, "Error: $err, Line: $lines, CID: $cid, year: $r->syear, me: $r->me, sic: $sic, Errmsg:$d->message \n");
                     }
                     
                }
            }
        }

     }
     
     fclose($fe);
    
     $res->proc = 100.0;
     send_message(++$msg_num, $res);
     $res->uploaded = $uploaded;     
     // $res->errors = $total_lines-$uploaded-1;
     $res->errfile = $fid;
     $db->query('call update_sales_totals');
     $res->stage = 'Import finished!';
     send_message('CLOSE', $res);
     unlink($tmp);
     fclose($f); 
   
?>
