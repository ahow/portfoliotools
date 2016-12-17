<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=T('Upload')?></li>
</ol>
      
<!-- Email Newsletter Subscription Form -->
<h2 class="uppertitle"><?=$this->cfg->title?></h2>
     
<div class="row">
 <form id="form" class="col-lg-12" enctype="multipart/form-data" method="POST">
 <h5 class="subscribetext">Select 3 files in CSV format</h5>

  <div class="form-group">
    <label for="company_list">Company List:</label>
    <input type="file" class="form-control" name="company_list" id="company_list">
  </div>
  <div id="preview_company_list"></div>

  <div class="form-group">
    <label for="sic_desc">SICDesc:</label>
    <input type="file" class="form-control" id="sic_desc" name="sic_desc">
  </div>
  <div id="preview_sic_desc"></div>

  <div class="form-group">
    <label for="division_details">Division Details:</label>
    <input type="file" class="form-control" id="division_details" name="division_details">
  </div>
  <div id="preview_division"></div>
  <!-- Button -->
  <div class="form-group">
    <center> <button id="button1id" name="button1id" class="btn btn-danger btn-lg btn-font">Upload</button> </center>
  </div>
  <!-- Button -->
  
  <div id="errors" style="display:none" class="alert alert-danger"></div>

 </form>  <!-- col-lg-5 -->
</div>  <!-- row -->


<?php
    

    if (isset($_FILES['sic_desc']))
    {   $tmp = tempnam('../uploads','sic-');
        $clist = (object)$_FILES['sic_desc'];
        
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  echo "<div class=\"alert alert-success\">Company SICDesc uploaded!</div>";

             $f = fopen($tmp,'r');
             $h = fgets($f);
             $a = explode(';',$h);
             $spl='';
             if (count($a)>1) $spl=';'; 
             else
             {  $a = explode(',',$h);
                if (count($a)>0) $spl=',';
             }
             
             if (count($a)!=7)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong SICDesc format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             $group_id = null;
             
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
             unlink($tmp);
             fclose($f); 
          }
    
        }
    }
    
    
    
    if (isset($_FILES['company_list']))
    {
        $clist = (object)$_FILES['company_list'];
        $tmp = tempnam('../uploads','company-');
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  echo "<div class=\"alert alert-success\">Company list uploaded!</div>";
             $f = fopen($tmp,'r');
             $h = fgets($f);
             $a = explode(';',$h);
             $spl='';
             if (count($a)>1) $spl=';'; 
             else
             {  $a = explode(',',$h);
                if (count($a)>0) $spl=',';
             }

            // echo "splitter: $spl<br>";
             
             if (count($a)!=9)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong Copmany format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             
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
                
                try
                { $db->insertObject('sales_companies',$r);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }

             }
             unlink($tmp);
             fclose($f); 
          }
    
        }
    }
    
    
    if (isset($_FILES['division_details']))
    {
        $clist = (object)$_FILES['division_details'];
        $tmp = tempnam('../uploads','divdetails-');
        if ($clist->error==0)
        { if (move_uploaded_file($clist->tmp_name, $tmp))
          {  echo "<div class=\"alert alert-success\">Division details uploaded!</div>";
             $f = fopen($tmp,'r');
             $h = fgets($f);
             $a = explode(';',$h);
             $spl='';
             if (count($a)>1) $spl=';'; 
             else
             {  $a = explode(',',$h);
                if (count($a)>0) $spl=',';
             }

            // echo "splitter: $spl<br>";
             $ncol = count($a);
             if ($ncol<7 || (($ncol-4) % 3)!=0)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong Division details format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             $r = new stdClass();
             $r->filename = $clist->name;
             $db->insertObject('sales_uploads',$r);
             $uid = $db->db->lastInsertId(); // upload ID
             
             // Remember fo the years values
             $years = array();
             
             for ($i=3; $i<$ncol; $i+=3)
             { //  $ya = preg_match('/\d+/', $a[$i], $matches, PREG_OFFSET_CAPTURE);
                // $years[$i]=$ya[0][0];
                $years[$i]= filter_var($a[$i], FILTER_SANITIZE_NUMBER_INT);
             }
             
             // print_r($years);             
             // echo "Upload Id: $uid<br>";
             
             $cid = '';
             
             
             while ($a = fgetcsv($f,0,$spl) )
             {  $division = trim( $a[0] );
                if ($division==1)
                {  $d = new stdClass();
                   $d->cid =  trim( $a[1] );
                   $d->sales = str_replace(',','.', trim( $a[($ncol-1)] ));
                   $d->upload_id = $uid;
                   $db->insertObject('sales_div_sales',$d);
                   $sale_id = $db->db->lastInsertId(); 
                }
                
                for ($i=3; $i<$ncol; $i+=3)
                {   $r = new stdClass();
                    $r->div_sale_id = $sale_id;
                    $r->division = $division;
                    $r->syear = $years[$i];
                    $r->me = trim( $a[$i] );
                    if ($r->me!='' && isset($a[$i+1]))
                    {
                        $r->sic = trim( $a[$i+1] );
                        $r->sales =str_replace(',','.', trim( $a[$i+2] ) );  
                        $db->insertObject('sales_divdetails',$r);
                    }
                }

             }
             
             $r = new stdClass();
             $r->success = true;
             $db->query('update sales_uploads set success=TRUE where id=:id',
             array('id'=>$uid));             
             echo "<div class=\"alert alert-success\">Division details imported!</div>";
             unlink($tmp);
             fclose($f); 
          }
    
        }
    }
    
    
?>
