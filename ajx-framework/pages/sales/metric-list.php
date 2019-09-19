<?php
    $this->displayCrumbs();
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();

if ($this->allow_edit)
{
?>
<h2 class="uppertitle"><?=$this->cfg->title?></h2>
<div id="metric-list">
</div>


<div class="model-list" data-model="/pages/sales/Model/metrics">        
        <table class="table table-striped selectable">
            <thead></thead>
            <tbody></tbody>
        </table>
        <div class="model-pager"></div>
</div>
        
     
<div class="row">
 <form id="form" class="col-lg-6" enctype="multipart/form-data" method="POST">
  <div class="form-group">
    <label for="add_metric">Add Metric (*.csv):</label>
    <input type="file" class="form-control" name="add_metric" id="add_metric">
  </div>
  <div id="preview_metric"></div>

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
    
    if (isset($_FILES['add_metric']))
    {   
        $clist = (object)$_FILES['add_metric'];
       
        if ($clist->error==0)
        { $tmp = mktempname(UPLOAD_PATH.'metric-');
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
             
             
             
             
             if (count($a)<2)
             {  fclose($f);
                unlink($tmp);
                echo "<div class=\"alert alert-danger\">Wrong Metric format!</div>";
                echo ($h);
                print_r($a);
                die();
             }
             
             $db = $this->cfg->db;
             $group_id = null;
             
             $r = new stdClass();
             $r->metric = implode(', ', array_slice($a,1));
             $r->description = $r->metric;
             $db->insertObject('sales_metrics',$r);
             $id = $db->db->lastInsertId();
             
             foreach($a as $k=>$v)
             {
                if ($k>0) 
                {   $r = new stdClass();
                    $r->col = $k;
                    $r->metric_id = $id;
                    $r->name = $a[$k];
                    $db->insertObject('sales_metrics_columns',$r);
                }
             }
             $max_col = count($a);

             while ($a = fgetcsv($f,0,$spl) )
             {  
                foreach($a as $k=>$v)
                if ($k>0 && $k<$max_col)
                {
                    $r = new stdClass();
                    $r->metric_id = $id;
                    $r->col = $k;
                    $r->isin = trim($a[0]);                
                    $r->val = str_replace(',','.', trim($a[$k]) );
                    
                    try
                    { if ($r->isin!='') $db->insertObject('sales_metrics_data',$r);
                    } catch(Exception $e)
                    { // echo $e->getMessage();
                    }
                }

             }
             fclose($f); 
             unlink($tmp);
             echo "<div class=\"alert alert-success\">Metric uploaded!</div>";
          }
    
        }
    }
    

} else $this->cfg->setError(T('ACCESS_DENIED'));    
?>
