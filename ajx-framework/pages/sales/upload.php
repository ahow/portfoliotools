<?php
   use PhpOffice\PhpSpreadsheet\IOFactory;
   require SYS_PATH.'/vendor/autoload.php';

    $this->displayCrumbs();
if ($this->allow_edit)
{
?>
      
<!-- Email Newsletter Subscription Form -->
<h2 class="uppertitle"><?=$this->cfg->title?></h2>
     
<div class="row">
 <form id="form" class="col-lg-12 form-horizontal" enctype="multipart/form-data" method="POST">
 <h5 class="subscribetext">Select 4 files in CSV format</h5>
  <div class="form-group">
    <label class="col-md-2" for="company_list">Company List:</label>
    <input type="file" class="col-md-4" name="company_list" id="company_list">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/companies.csv" >Download company list</a>
  </div>
  
  <div class="form-group">
        <div id="preview_company_list" class="col-md-12"></div>
  </div>

  <div class="form-group">
    <label class="col-md-2" for="sic_desc">SICDesc:</label>
    <input type="file" class="col-md-4" id="sic_desc" name="sic_desc">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/sicdesc.csv">Download SICDesc</a>
  </div>

  <div class="form-group">
    <div id="preview_sic_desc" class="col-md-12"></div>
  </div>
  
  <div class="form-group">
    <label class="col-md-2" for="division_details">Division Details:</label>
    <input type="file" id="division_details" class="col-md-4" name="division_details">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/datadetails.csv">Download division details</a>
  </div>
  
  <div class="form-group">
    <div id="preview_division" class="col-md-12"></div>
  </div>
  
  <div class="form-group">
    <label class="col-md-2" for="isin_matching">ISIN matching:</label>
    <input type="file" class="col-md-4" id="isin_matching" name="isin_matching">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/isin-matching.csv">Download ISIN matching</a>
   </div>

     
  <div class="form-group">
    <label class="col-md-2" for="company_theme">Company theme metrics:</label>
    <input type="file" class="col-md-4" id="company_theme" name="company_theme">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/company-theme-metrics.xlsx">Download *.xlsx</a>
  </div>
  
  <!--
  <div class="form-group">
    <label class="col-md-2" for="full_xls">All data from Excel file:</label>
    <input type="file" class="col-md-4" id="full_xls" name="full_xls">
    <div class="col-md-3"></div>
    <a class="btn btn-primary col-md-3" href="/html.php/pages/sales/full_xls.xls">Download *.xls</a>
  </div>
   -->

  <div class="form-group">
    <div id="preview_isin" class="col-md-12"></div>
  </div>
  
  <div class="checkbox">
     <label><input type="checkbox" name="clear_data" value="" title="">Clear old data</label>
     <div  class="alert alert-warning"><b>WARNING</b> If the checkbox is set and SICDesc file selected then DivisionDetails will be cleared too</div>
  </div>

  <!-- Button -->
  <div class="form-group">
    <center> <button id="button1id" name="button1id" class="btn btn-danger btn-lg btn-font">Upload</button> </center>
  </div>

  <!-- Button -->
  
  <div id="errors" style="display:none" class="alert alert-danger"></div>

 </form>  <!-- col-lg-5 -->
</div>  <!-- row -->


<?php
    // clear data
    $clear =  (post('clear_data','not')!='not');

    function mktempname($prefix)
    { return $prefix.date('YmdHis').'.'.rand(1,10000);
    }

    if (isset($_FILES['sic_desc']))
    {   
        $clist = (object)$_FILES['sic_desc'];
       
        if ($clist->error==0)
        { $tmp = mktempname(UPLOAD_PATH.'sic-');
          if (move_uploaded_file($clist->tmp_name, $tmp))
          {  $f = fopen($tmp,'r');
             $h = fgets($f);
             $a = explode(';',$h);
             $spl='';
             if (count($a)>1) $spl=';'; 
             else
             {  $a = explode(',',$h);
                if (count($a)>0) $spl=',';
             }
             
             if (count($a)<=7)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong SICDesc format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             
             
             $db = $this->cfg->db;
             $group_id = null;
             
             if ($clear) 
             {  $db->query('delete from sales_divdetails');
                $db->query('delete from sales_sic where id>0');
                $db->query('delete from sales_industry_groups where id>0');
             }
             
             $ha = array_slice($a,7);
             if (trim($ha[count($ha)-1])=='') array_pop($ha); // remove last empty element             
             foreach($ha as $k=>$v) {
                $ha[$k] = trim( trim($v) ,'"');                
             }
             $db->query('update sales_exposure set headers=:headers',             
             array( 'headers'=>trim( implode(';',$ha),'"') ));             
             while ($a = fgetcsv($f,0,$spl) )
             {  $r = new stdClass();
                $r->division = $a[0];
                $r->major_group = $a[1];
                $r->id = $a[2];
                $r->industry_group = $a[3];
                if (trim($r->id)!='') $group_id=$r->id;
                
                $sic = new stdClass();
                $sic->id = $a[4];
                $sic->name = $a[5];
                $sic->description  = $a[6];
                $sic->industry_group_id = $group_id;
                $sic->exposure  = implode(';', array_slice($a,7) );
                
                /*
                $sic->climate_change = $a[7];
                $sic->demographics = $a[8];
                $sic->regulation = $a[9];
                $sic->another_theme = $a[10];
                */
                
                try
                { if ($r->id!='') $db->insertObject('sales_industry_groups',$r);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }

                try
                {  $db->insertObject('sales_sic',$sic);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }
                

             }
             fclose($f); 
             unlink($tmp);
             echo "<div class=\"alert alert-success\">Company SICDesc uploaded!</div>";
          }
    
        }
    }
    
    function nullable($v)
    { $v = trim($v);
      if ($v=='') return NULL;
      else return $v;
    }
    
    class CSVField 
    {  var $ks;
       function __construct($header)
       { $this->ks = [];
         foreach ($header as $k=>$v) {                
            $this->ks[ preg_replace('/[\ ]+/','_', strtolower($v) ) ] = $k;
         }          
       }

       function get($a, $name, $is_nullable = false)
       {  if (!isset($this->ks[$name])) return null;
          if (isset($a[$this->ks[$name]])) 
          {
            $v = trim( $a[$this->ks[$name]] );
            if ($is_nullable && $v=='') return null;
            return $v;
          }          
          return null;
       }
       
    }

    function setCompanyListRow(&$r, $fld,  $a)
    { $r->cid = $fld->get($a, 'cid');
      $r->name = $fld->get($a, 'name');
      $r->industry_group = $fld->get($a, 'industry_group');
      $r->industry = $fld->get($a, 'industry');
      $r->sector = $fld->get($a, 'sector');
      $r->subsector = $fld->get($a, 'subsector');
      $r->country = $fld->get($a, 'country');
      $r->isin = $fld->get($a, 'isin');
      $r->region = $fld->get($a, 'region');
      $r->sales = $fld->get($a, 'sales', true);                
      $r->market_cap = $fld->get($a, 'market_cap', true);
      $r->sales_growth = $fld->get($a, 'sales_growth', true);
      $r->roic =  $fld->get($a, 'roic', true);
      $r->pe =  $fld->get($a, 'pe', true); 
      if ($r->pe==null) $r->pe = $fld->get($a, 'price_to_earnings', true); // alias      
      $r->EBITDA_growth = $fld->get($a, 'ebitda_growth', true);
      $r->ROE = $fld->get($a, 'roe', true);                
      $r->evebitda = $fld->get($a, 'evebitda', true);
      if ($r->evebitda==null) $r->evebitda=$fld->get($a, 'ev_to_ebitda', true); //alias
       
      //  [sustainex] => 23 [reviewed ] => 24 )
      $r->yield =  $fld->get($a, 'yield', true);
      $r->price_to_book = $fld->get($a, 'price_to_book', true);
      $r->reinvestment = $fld->get($a, 'reinvestment', true);
      $r->research_and_development = $fld->get($a, 'research_and_development', true);
      $r->net_debt_to_ebitda  = $fld->get($a, 'net_debt_to_ebitda', true);
      $r->CAPE = $fld->get($a, 'cape', true);
      $r->sustain_ex = $fld->get($a, 'sustainex', true);
      $r->payout = $fld->get($a, 'payout', true);  //  Obsolete, should be removed
      $r->reviewed= $fld->get($a, 'reviewed', true);  
      
    }
    
    function getFileExtention($file)
    { $ext = strtolower( substr($file, -4) );
      if (strlen($ext)>0 && $ext{0}!=='.') $ext='.'.$ext;
      return $ext;
    }

    if (isset($_FILES['company_list']))
    {
        $clist = (object)$_FILES['company_list'];
        $ext = getFileExtention($clist->name);        
        $tmp = mktempname(UPLOAD_PATH.'company-').$ext;
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  $db = $this->cfg->db;

             if ($ext=='.csv')
             {
               $f = fopen($tmp,'r');
               $h = fgets($f);
               $a = explode(';',$h);
               $spl='';
               // Delimeter auto detection 
               if (count($a)>1) $spl=';'; 
               else
               {  $a = explode(',',$h);
                  if (count($a)>0) $spl=',';
               }
  
               $fld = new CSVField($a);
               // echo "splitter: $spl<br>";
               
               $n_col = count($a);
               if ($n_col<16 || ($n_col>17 && $n_col!=25))
               {  fclose($f);
                  unlink($tmp);
                  echo "<div class=\"alert alert-danger\">Wrong Company format!  ($n_col)</div>";
                  // echo ($h);
                  print_r($a);
                  die();
               }
               
               if ($clear) $db->query('delete from sales_companies');
                            
               while ($a = fgetcsv($f,0,$spl) )
               { $r = new stdClass();
                 setCompanyListRow($r, $fld, $a);              
                 try
                 { $db->insertObject('sales_companies',$r);
                 } catch(Exception $e)
                 { echo $e->getMessage();
                 }
               }
               fclose($f); 
               unlink($tmp);
               echo "<div class=\"alert alert-success\">Company list uploaded!</div>";
             } else 
             if ($ext=='.xlsx')
             { 
               $inputFileType = 'Xlsx';               ;
               $sheetname = 'CompanyList';
               $reader = IOFactory::createReader($inputFileType);
               $reader->setLoadSheetsOnly($sheetname);
               $reader->setReadDataOnly(true);
               $spreadsheet = $reader->load($tmp);               
               $loadedSheetNames = $spreadsheet->getSheetNames();
               if (count($loadedSheetNames)>0)
               {  if ($clear) $db->query('delete from sales_companies');
                  $data = $spreadsheet->getSheetByName($sheetname)->toArray();
                  $fld = new CSVField($data[0]);
                  for ($i=1; $i<count($data); $i++){
                     $a = $data[$i];
                     $r = new stdClass();
                     setCompanyListRow($r, $fld, $a);              
                     try
                     { $db->insertObject('sales_companies',$r);
                     } catch(Exception $e)
                     { echo $e->getMessage();
                     }
                  }                  
               }
               unlink($tmp);
               echo "<div class=\"alert alert-success\">Company list uploaded!</div>";
             }
          }
    
        }
    }
    
    
    
    
    if (isset($_FILES['division_details']))
    {
        $clist = (object)$_FILES['division_details'];        
        $tmp = mktempname(UPLOAD_PATH.'divdetails-');
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  // echo "<div class=\"alert alert-success\">Division details file uploaded! Import started!</div>";
             ?>
            <div class="progress" id="pb_details" data-path="/html.php/pages/sales/uploaddetails?clear=<?=$clear?>&amp;tmp=<?=urlencode($tmp)?>">
                <div class="progress-bar progress-bar-striped active" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            
             <?php
             // Auto start importing
             
          } 
    
        } 
        /*else 
        { // echo "<div class=\"alert alert-danger\">Upload Error! Please, check php.ini uploading limits.</div>";
        }
        */
    }

    if (isset($_FILES['company_theme']))
    {
        $clist = (object)$_FILES['company_theme'];  
        $ext = getFileExtention($clist->name);      
        $tmp = mktempname(UPLOAD_PATH.'company-theme-').$ext;
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  // echo "<div class=\"alert alert-success\">Company theme metrics details file uploaded! Import started!</div>";
             ?>
            <div class="progress" id="pb_company_theme" data-path="/html.php/pages/sales/upload-company-theme?clear=<?=$clear?>&amp;tmp=<?=urlencode($tmp)?>">
                <div class="progress-bar progress-bar-striped active" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>
            </div>
            
             <?php
             // Auto start importing
             
          } 
    
        } 
        /*else 
        { // echo "<div class=\"alert alert-danger\">Upload Error! Please, check php.ini uploading limits.</div>";
        }
        */
    }
    
    if (isset($_FILES['isin_matching']))
    {
        $clist = (object)$_FILES['isin_matching'];
        $tmp = mktempname(UPLOAD_PATH.'isin-matching-');
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  $f = fopen($tmp,'r');
             $h = fgets($f);
             $a = explode(';',$h);
             $spl='';
             if (count($a)>1) $spl=';'; 
             else
             {  $a = explode(',',$h);
                if (count($a)>0) $spl=',';
             }

            // echo "splitter: $spl<br>";
             
             if (count($a)!=2)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong ISIN matching format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             
             if ($clear) 
             {  $db->query('delete from sales_isin_matching');
             }
             
             while ($a = fgetcsv($f,0,$spl) )
             {  $r = new stdClass();
                $r->isin= trim( $a[0] );
                $r->isin_alias = trim ($a[1] );
                
                try
                { $db->insertObject('sales_isin_matching',$r);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }

             }
             fclose($f); 
             unlink($tmp);
             echo "<div class=\"alert alert-success\">ISIN matching list uploaded!</div>";
          }
    
        }
    }
    
} else $this->cfg->setError(T('ACCESS_DENIED'));    
?>
