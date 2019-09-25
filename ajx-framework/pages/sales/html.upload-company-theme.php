<?php
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache'); 
    ob_start();
    
    use PhpOffice\PhpSpreadsheet\IOFactory;
    require SYS_PATH.'/vendor/autoload.php';
    
    $msg_num = 0;
    
    function send_message($id, $d) 
    {   echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;
        if (ob_get_level() > 0) ob_flush();        
    }

    function getFileExtention($file)
    { $ext = strtolower( substr($file, -4) );
      if (strlen($ext)>0 && $ext{0}!=='.') $ext='.'.$ext;
      return $ext;
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
     
     $ext = getFileExtention($tmp);

     if ($ext!=='.xlsx') 
     {  $res->errmsg = "Wrong extention $ext!";
        send_message('ERROR', $res);
        die();
     }

    $uploaded = 0;
    $db = $this->db;

    $sheetname = 'CompanyThemeMetrics';
    $reader = IOFactory::createReader('Xlsx');
    $reader->setLoadSheetsOnly($sheetname);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($tmp);               
    $loadedSheetNames = $spreadsheet->getSheetNames();
    if (count($loadedSheetNames)>0)
    { if ($clear) 
      {         
         $db->query('delete from sales_company_theams');
         $db->query('delete from sales_theams');
      } 
       $data = $spreadsheet->getSheetByName($sheetname)->toArray();

       $total = count($data)-1;
       $res->total = $total;
       
       send_message(++$msg_num, $res);
       unset($res->total);

       $res->stage = 'excel opened';
       send_message(++$msg_num, $res);
       try
       {  
            $h = $data[0];
            $lines = 0;
            
            // Fill theams values
            $theams = [];
            for ($i=3; $i<count($h); $i++)
            {  $theam = $h[$i];
                $qr=$db->query('select id, theam from sales_theams where theam=:theam',
                        ['theam'=>$theam]);
                $r = $db->fetchSingle($qr);          
                if (!empty($r)) 
                        $theams[$i]=$r;
                else {
                    $db->query('insert into sales_theams (theam) values (:theam) '
                    ,['theam'=>$theam]);
                    $db->db->lastInsertId();
                    $r = (object)['id'=>$db->db->lastInsertId(), 'theam'=>$theam];
                    $theams[$i]=$r;
                }          
            }
            
            $CID = 0;
            $NAME = 1;
            $ISIN = 2;

         
            for ($i=1; $i<count($data); $i++)
            {   $r = $data[$i];
                $lines++;
                if (($lines % 100) == 0)
                {  $res->proc = number_format(($lines/$total_lines)*100, 2, '.', '');
                    send_message(++$msg_num, $res);
                }
                $nr = new stdClass();
                $nr->cid = $r[$CID];
                for ($j=3; $j<count($h); $j++)
                {  $nr->theam_id = $theams[$j]->id;
                   $nr->theam_value = $r[$j];
                   if ($nr->theam_value!==null)
                         $db->insertObject('sales_company_theams', $nr);
                }
            }
       } catch (Exception $e) {
          $res->errmsg = $e->getMessage();
          send_message('ERROR', $res);
          die();
       }         

    }
         
     $res->proc = 100.0;
     send_message(++$msg_num, $res);
     $res->uploaded = $uploaded;     
     $res->stage = 'Import finished!';
     send_message('CLOSE', $res);
     unlink($tmp);
     // fclose($f); 
   
?>
