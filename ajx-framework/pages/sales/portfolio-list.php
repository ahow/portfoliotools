<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>
<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();    
if ($this->allow_edit)
{
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>
<div id="portfolio-list">
</div>


<div class="model-list" data-model="/pages/sales/Model/portfolio">        
        <table class="table table-striped selectable">
            <thead></thead>
            <tbody></tbody>
        </table>
        <div class="model-pager"></div>
</div>
        
     
<div class="row">
 <form id="form" class="col-lg-6" enctype="multipart/form-data" method="POST">
  <div class="form-group">
    <label for="add_portfolio">Add Portfolio (*.csv):</label>
    <input type="file" class="form-control" name="add_portfolio" id="add_portfolio">
  </div>
  <div id="preview_portfolio"></div>

  <!-- Button -->
  <div class="form-group">
    <center> <button id="button1id" name="button1id" class="btn btn-danger btn-lg btn-font">Upload</button> </center>
  </div>

  <!-- Button -->
  
  <div id="errors" style="display:none" class="alert alert-danger"></div>

 </form>  <!-- col-lg-5 -->
</div>  <!-- row -->


<?php

    function mktempname($prefix)
    { return $prefix.date('YmdHis').'.'.rand(1,10000);
    }

    function nullable($v)
    { $v = trim($v);
      if ($v=='') return NULL;
      else return $v;
    }
    
    if (isset($_FILES['add_portfolio']))
    {   
        $clist = (object)$_FILES['add_portfolio'];
       
        if ($clist->error==0)
        { $tmp = mktempname('../uploads/portfolio-');
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
             
             
             
             
             if (count($a)!=2)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong Portfolio format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             $group_id = null;
             
             $r = new stdClass();
             $r->portfolio = $a[1];
             $r->description = $r->portfolio;
             $db->insertObject('sales_portfolio',$r);
             $id = $db->db->lastInsertId();

             while ($a = fgetcsv($f,0,$spl) )
             {  $r = new stdClass();
                $r->portfolio_id = $id;
                $r->isin = trim($a[0]);
               
                // replace isin by alias if needed 
                $qr = $db->query('select isin from sales_isin_matching where isin_alias=:isin',
                array('isin'=>$r->isin));
                $row = $db->fetchSingle($qr);
                if (!empty($row)) $r->isin = $row->isin;

                $r->val = str_replace(',','.', trim($a[1]) );
                
                try
                { if ($r->isin!='') $db->insertObject('sales_portfolio_data',$r);
                } catch(Exception $e)
                { // echo $e->getMessage();
                }

             }
             fclose($f); 
             unlink($tmp);
             echo "<div class=\"alert alert-success\">Portfolio uploaded!</div>";
          }
    
        }
    }
    

} else $this->cfg->setError(T('ACCESS_DENIED'));
?>

