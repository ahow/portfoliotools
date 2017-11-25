<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=T('Upload')?></li>
</ol>

<?php
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
             $db->query('update sales_exposure set headers=:headers',
             array( 'headers'=>implode(';',$ha) ) );
             
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
    
    if (isset($_FILES['company_list']))
    {
        $clist = (object)$_FILES['company_list'];
        $tmp = mktempname(UPLOAD_PATH.'company-');
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
             
             $n_col = count($a);
             if ($n_col<16 || $n_col>17)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong Company format!  ($n_col)</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             
             if ($clear) 
             {  $db->query('delete from sales_companies');
             }
             
             while ($a = fgetcsv($f,0,$spl) )
             {  $r = new stdClass();
                $r->cid = trim( $a[0] );
                $r->name = trim ($a[1] );
                $r->industry_group = trim ($a[2] );
                $r->industry = trim ($a[3] );
                $r->sector = trim ($a[4] );
                $r->subsector = trim ($a[5] );
                $r->country = trim ($a[6] );
                $r->isin = trim ($a[7] );
                $r->region = trim ($a[8] );
                $r->sales = nullable($a[9] );
                $r->market_cap = nullable($a[10] );
                $r->sales_growth = nullable($a[11] );
                $r->roic = nullable ($a[12] );
                $r->pe = nullable ($a[13] );
                $r->evebitda = nullable ($a[14] );
                $r->payout = nullable ($a[15] );
                if (isset($a[16]) && ($a[16]==1 || $a[16]=='true') ) $r->reviewed=1;
                
                try
                { $db->insertObject('sales_companies',$r);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }

             }
             fclose($f); 
             unlink($tmp);
             echo "<div class=\"alert alert-success\">Company list uploaded!</div>";
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
