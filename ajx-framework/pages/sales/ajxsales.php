<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include(SYS_PATH.'lib/params.php');
 // include(SYS_PATH.'lib/phpmailer.php');
 include(SYS_PATH.'lib/ajaxmodel.php');
 
 class ajxsales extends wAjaxModel
 {  
    function ajxModel()
    {   $this->includePageLocales(__DIR__);
        if (!isset($this->cfg->user) || !isset($this->cfg->user->user->id))
        return $this->error(T("ERR_NOT_AUTHORIZED"), true);
        $this->processModel(__DIR__);
    }
    
    function beforeDivisionUpdate(&$row, $keys)
    {   if (isset($this->cfg->user) && isset($this->cfg->user->user->id))
        {  $row->modified_by = $this->cfg->user->user->id;
        }
        $row->modified = date("Y-m-d H:i:s");
    }
    
    function beforeUpdateCompany(&$row, $keys)
    {   if (isset($this->cfg->user) && isset($this->cfg->user->user->id))
        {  $row->modified_by = $this->cfg->user->user->id;
        }
        $row->modified = date("Y-m-d H:i:s");
    }
    
    // afterLoad trigger
    function afterLoadSIC()
    {   $db = $this->cfg->db;
        $qr = $db->query('select headers from sales_exposure');
        $a = explode(';', $db->fetchSingleValue($qr));
        $cols = array();
        foreach ($a as $k=>$v) $cols[]="e$k";
        $this->res->titles = array_merge($this->res->titles, $a);
        $this->res->columns = array_merge($this->res->columns, $cols);
        foreach($this->res->rows as $r)
        {  $d = explode(';', $r->exposure);
           foreach ($cols as $k=>$v) 
           {   $k = trim($k);
               if (isset($d[$k]))
               {   $nv = $d[$k];
                   if (is_numeric($nv) && 1*$nv>0) $nv='+'.$nv;
                   $r->$v = $nv;
               }
           }
        }
    }
    
    function loadSettings($key)
    {  $db = $this->cfg->db;
       $qr = $db->query("select json from settings where name=:name",array('name'=>$key));
       $s = $db->fetchSingleValue($qr);
       if ($s!=null) return json_decode($s);
       return null;
    }
    
    function saveSettings($key)
    {  $db = $this->cfg->db;
       $params = (object)$_POST;
       if (isset($params->data))
       try
       { $qr = $db->query("insert into settings (name,json) values (:name,:json)",
           array('name'=>$key,'json'=>json_encode($params->data)));
       } catch(Exception $e)
       {  $qr = $db->query("update settings set json=:json where name=:name",
           array('name'=>$key,'json'=>json_encode($params->data)));
       }  
       $this->res->info = T('SAVED');
    }

    function runSQL($scfile)
    {   $db = $this->cfg->db;
        $f = fopen($scfile,'r');
        $delim = ';'; 
        $sql = '';
        $a = array();

        while ($s = fgets($f))
        {   $s = trim($s);
            if ($s!='')
            {  // remove comments
               $uncom = preg_replace('/--(.)*/i', '', $s);
               
               if (trim($uncom!=''))
               { if (($p=stripos($uncom,'DELIMITER'))!==false)
                 {  $arg = substr($uncom,$p+10);
                    $d = trim($arg);
                    $uncom='';
                    $l = strlen($d);
                    if ($l>0 && $l<3) $delim=$d;
                 }
                 
                 if ( ($p=strpos($uncom, $delim)) !==false)                  
                 {  $ds = strlen($delim);
                    $sql.=substr($uncom, 0, $p);
                    $a[] = $sql;
                    $sql = ''; //
                    $uncom = substr($uncom, $p+$ds);                    
                  }
               }
               $sql.=$uncom;               
            }
        }
        fclose($f);

        $this->res->queries = $a;
        $this->res->cfg = $this->cfg;
    }
       
 
 /*   
    function ajxSQlScript()
    {   $file = post('file');
        $this->res->file = $file;
        $this->runSQL($file);
        echo json_encode($this->res);
    }
*/
  
    function ajxSaveSummaryDescriptions()
    {  $this->saveSettings('SummaryDescriptions');
       echo json_encode($this->res);
    }
 

    function ajxLoadSummaryDescriptions()
    {  $this->res->row = $this->loadSettings('SummaryDescriptions');
       echo json_encode($this->res);
    }

    function ajxLoadPortfolioSummariesSettings()
    {  $this->res->row = $this->loadSettings('PortfolioSummaries');
       echo json_encode($this->res);
    }
        
    function ajxSavePortfolioSummariesSettings()
    {  $this->saveSettings('PortfolioSummaries');
       echo json_encode($this->res);
    }
    
    function ajxSaveSnapshotSummariesSettings()
    {  $this->saveSettings('SnapshotSummaries');
       echo json_encode($this->res);
    }
    
    function ajxLoadSnapshotSummariesSettings()
    {  $this->res->row = $this->loadSettings('SnapshotSummaries');
       echo json_encode($this->res);
    }
    
    function getSnapshot($metric, $portfolio, $comparison)    
    {  $db = $this->cfg->db;       
       $res = new stdClass();
       
       $db->query('set @pf=:pf', array('pf'=>$portfolio));
       $db->query('set @mt=:mt', array('mt'=>$metric));
          
       $qr = $db->query('select d.isin, d.val as pval, c.name, sum(m.val) as val
        from sales_portfolio_data d
       join sales_companies c on d.isin=c.isin
       join sales_metrics_data m on m.isin=c.isin and m.metric_id=@mt
       where d.portfolio_id=@pf
       group by 1,2,3');
       $res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
       $pfs = 0;
       $pvs = 0.0; // total of portfolio values
       foreach($res->rows as $r)
       {  $pfs+=1.0*$r->val*$r->pval;
          $pvs+=1.0*$r->pval;
       }
       $res->pfsum = $pfs/$pvs; // corrected portfolio product summ
         
       $db->query('set @pf=:pf', array('pf'=>$comparison));
       $qr = $db->query('select d.isin, d.val as pval, c.name, sum(m.val) as val
        from sales_portfolio_data d
       join sales_companies c on d.isin=c.isin
       join sales_metrics_data m on m.isin=c.isin and m.metric_id=@mt
       where d.portfolio_id=@pf
       group by 1,2,3');
       $rows= $qr->fetchAll(PDO::FETCH_OBJ);
       $cms = 0;
       $pvs = 0.0; // total of portfolio values
       foreach($rows as $r)
       {  $cms+=1.0*$r->val*$r->pval;
          $pvs+=1.0*$r->pval;
       }
       $res->cmsum = $cms/$pvs; // corrected comparison product summ
       
       return $res;       
    }
    
    function ajxESGData()
    { $params = (object)$_POST;
      if (isset($params->pf_id) && isset($params->mt_id) && isset($params->comp_id))
      {  $res = $this->getSnapshot($params->mt_id, $params->pf_id, $params->comp_id);
         $this->res->rows = $res->rows;
         $this->res->cmsum = $res->cmsum;
         $this->res->pfsum = $res->pfsum;
      }
      echo json_encode($this->res);
    }
    
    function ajxGetSnapshots()
    {  $params = (object)$_POST;
       $db = $this->cfg->db;
       
       if (isset($params->pf_id))
       { $a = array();
         $ss = $this->loadSettings('SnapshotSummaries');
         if (isset($ss->metrics) && isset($ss->comparison_id))
         foreach ($ss->metrics as $m) 
         {   $m->rows = $this->getSnapshot($m->id, $params->pf_id, $ss->comparison_id);
             $qr = $db->query("select metric from sales_metrics where id=:id",array('id'=>$m->id));
             $m->metric = $db->fetchSingleValue($qr);
             // $m->comp_id = $ss->comparison_id;
             // $m->pf_id = $params->pf_id;
             $a[] = $m;
         }
         $this->res->rows = $a;
       }
       echo json_encode($this->res);
    }
    
    function ajxGetPortfolioName()
    {  $db = $this->cfg->db;
       $params = (object)$_POST;
       $qr = $db->query('select * from sales_portfolio p where id=:id',
       array('id'=>$params->id));
       $this->res->row = $db->fetchSingle($qr);
       echo json_encode($this->res); 
    }
    
    function ajxLoadPortfolioSummaries()
    {   $db = $this->cfg->db;
        $params = (object)$_POST;
        if (isset($params->id) && $params->id!='')
        {
          $qr = $db->query('select s.*, p.portfolio, p.description as pdescr 
 from sales_portfolio_summaries s
 join sales_portfolio p on s.portfolio_id=p.id
 where s.id=:id',array('id'=>(1*$params->id)) );
          $r = $db->fetchSingle($qr);
            if (!empty($r))
            {  $d = json_decode($r->json);
               $d->description = $r->description;
               $d->portfolio = trim($r->portfolio);
               $d->portfolio_id = $r->portfolio_id;
               $this->res->row = $d;
            }
        }
        echo json_encode($this->res);
    }
    
    function ajxSavePortfolioSummaries()
    {   $db = $this->cfg->db;
        $params = (object)$_POST;
        $row = new stdClass();
        $row->portfolio_id = $params->portfolio_id;
        $row->description = $params->description;
        unset($params->portfolio_id);
        unset($params->description);
        $row->json = json_encode($params);        
        if (isset($params->id))
        {   $row->id = $params->id;
            $qr = $db->query('update sales_portfolio_summaries 
            set portfolio_id=:portfolio_id,
            description=:description,json=:json
            where id=:id',$row);
            $this->res->insert_id = $row->id;
        }
        else 
        {       
            $qr = $db->query('insert into sales_portfolio_summaries 
            (portfolio_id,description,json) values
            (:portfolio_id, :description, :json)',$row);
            $this->res->insert_id = $db->db->lastInsertId();
        }
        $this->res->info = T('SAVED');
        echo json_encode($this->res);
    }
    
    function ajxForm1()
    {   $db = $this->cfg->db;
        $params = (object)$_POST;
        $qr = $db->query('select d.cid, d.division as number, ig.division as sic_division,
         ig.major_group, d.syear, d.me, d.sic, d.sales,sic.name as sicname, ig.industry_group
         from sales_divdetails d        
        join sales_sic sic on d.sic=sic.id
        join sales_industry_groups ig on sic.industry_group_id=ig.id
         where cid=:cid and d.syear<=:year order by d.division, d.syear',$params);
         
        $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($this->res);
    }
    
    function ajxMarketRanking()
    {   $params = (object)$_POST;
        $db = $this->cfg->db;
        $prm = new stdClass();
        $having = '';
        $region = '';
        if (isset($params->sic))
        {  $prm->sic =  $params->sic;
           $prm->year_max = $params->year;
           $prm->year_min = $prm->year_max-2;
        } else 
        if (isset($params->subsector))
        {  $prm->subsector =  $params->subsector;
           $prm->year_max = $params->year;
           $prm->year_min = $prm->year_max-2;
        } else  return $this->error(T('SIC_OR_SUBSECTOR_NOT_FOUND'), __LINE__);
        
        if (isset($params->min_size)) 
        {  $having = ' having sum(d.sales)>:min_size ';
           $prm->min_size =  $params->min_size;
        }
        
        if (isset($params->region)) 
        {  if ($params->region!='Global')
           {
               $region = '  and c.region=:region ';
               $prm->region =  $params->region;
           }
        }
                
        $sql = "select d.cid,c.name,d.syear,sum(d.sales) as tsales from sales_divdetails d
join sales_companies c on d.cid=c.cid
where d.sic=:sic and (d.syear between :year_min and :year_max) $region
group by 1,2,3
$having
order by 3 desc,4 desc";

       if (isset($prm->subsector)) $sql = "select d.cid,c.name,d.syear,sum(d.sales) as tsales from sales_divdetails d
join sales_companies c on d.cid=c.cid
where c.subsector=:subsector and (d.syear between :year_min and :year_max) $region
group by 1,2,3
$having
order by 3 desc,4 desc";

       $qr = $db->query($sql, $prm);
       $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
       echo json_encode($this->res);
    }
   
   
   function ajxMarketSummarySicTotals_old()
   {    $params = (object)$_POST;
        $db = $this->cfg->db;
        $prm = new stdClass();
        if (isset($params->sic)) $prm->sic=$params->sic;
        else return $this->error("ERR_SIC_NOT_FOUND", true);        
        if ($params->sic=='') return $this->error("ERR_SIC_EMPTY", true);        
        $sql = "select syear, sum(sales) as tsales, sum(ebit) as tebit,
         sum(assets) as tassets,  sum(capex) as tcapex 
        from sales_divdetails where sic=:sic group by 1";
        $qr = $db->query($sql, $prm);
        $this->res->rows = $qr->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($this->res);
   }
 
   function ajxMarketSummarySicTotals()
   {    $params = (object)$_POST;
        $db = $this->cfg->db;

        $db->query('call select_single_sic(:sic)', 
          $this->getPostParams('sic'));
        
        if ( ($this->res->lrows=$this->growth3yrCalculation(post('lhs')))===false &&
             ($this->res->lrows=$this->growthCalculation(post('lhs')))===false &&
             ($this->res->lrows=$this->SumBySumCalculation(post('lhs')))===false &&
             ($this->res->lrows=$this->growth5yrCalculation(post('lhs')))===false
           )
        {   $qr = $db->query('call summary_by_sics_by_years(:lhs,:region)',  
            $this->getPostParams('lhs,region'));
            
            $this->res->lrows = $qr->fetchAll(PDO::FETCH_OBJ);
            $qr->closeCursor();
        }

        if (post('lhs')==post('rgs')) $this->res->rrows = $this->res->lrows;
        else 
        {   if ( ($this->res->rrows=$this->growth3yrCalculation(post('rhs')))===false &&
                 ($this->res->rrows=$this->growthCalculation(post('rhs')))===false &&
                 ($this->res->rrows=$this->SumBySumCalculation(post('rhs')))===false &&
                 ($this->res->rrows=$this->growth5yrCalculation(post('rhs')))===false
            )
            {
                $qr2 = $db->query('call summary_by_sics_by_years(:rhs,:region)',  
                  $this->getPostParams('rhs,region'));
                $this->res->rrows = $qr2->fetchAll(PDO::FETCH_OBJ);
                $qr2->closeCursor();
            }
        }
        echo json_encode($this->res);
   }
   // Code obsolete. should be removed
   function getMarketSummaryBySic($sic=null, $debug=false)
   {    $params = (object)$_POST;
        $db = $this->cfg->db;
        $prm = new stdClass();
        $region = '';        
        $wsize = '';
        $region = '';        
        
        $prm = new stdClass();
        $res = new stdClass();
        
        if ($sic!=null) $prm->sic=$sic; 
        else if (isset($params->sic)) $prm->sic=$params->sic;
        else $this->error("ERR_SIC_NOT_FOUND", true);
        
        
        if (isset($params->region)) 
        {  if ($params->region!='Global')
           {
               $region = '  and c.region=:region ';
               $prm->region =  $params->region;
           }
        }
        
        if (isset($params->min_size) && $params->min_size!='')
        {  $wsize = ' and d.sales>:min_size ';
           $prm->min_size = $params->min_size;
        }
        
        if ($debug) $res->dbg = "-------------- Stability ------------------\n";
        
        // Stability calculations first we will get max and min year
        $sql = 
"select 
min(d.syear) as minyear, max(d.syear) as maxyear
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
where d.sic=:sic $region $wsize";
        $qr = $db->query($sql, $prm);
        $yr = $db->fetchSingle($qr);
        if ($debug) $res->dbg.="years: $yr->minyear - $yr->maxyear\n\n";
        
      
        
        if (!empty($yr))
        {   $years = array();
            $yr->minyear=1*$yr->minyear;
            $yr->maxyear=1*$yr->maxyear;
            for ($y=$yr->minyear; $y<=$yr->maxyear; $y++)
            {   $sql = "select 
   c.cid,c.name,sum(d.sales)
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
where d.sic=:sic $region and d.syear=$y $wsize
group by c.cid, c.name
order by d.sales desc
limit 20";
                $years[$y] = array();
                $qr = $db->query($sql, $prm);
                $i=1;                
                if ($debug) $res->dbg.="\nSQL: $sql\n\n";
                if ($debug) $res->dbg.="Params: ".print_r($prm, true)."\n";
                while ( $r = $db->fetchSingle($qr) )
                {   $years[$y][$r->cid] = $i;
                    $i++;
                    if ($debug) $res->dbg.=implode("\t",(array)$r)."\n";
                }
                
            }
      
                
            
            $changes = array();
            $n = 0;
            $sum = 0;
                        
            for ($y=$yr->maxyear; $y>$yr->minyear; $y--)
            {  $yd = ''.$y.'-'.($y-1);
                $changes[$yd]= array();
                foreach($years[$y] as $k=>$v)
                { if (!isset($years[$y-1][$k])) 
                  {
                     $df = 5;
                  }
                  else 
                  {  $df = $years[$y][$k] - $years[$y-1][$k];                      
                  }
                  $changes[$yd][$k]=$df;
                  $n++;
                  $df = abs($df);
                  if ($df>5) $df=5;
                  $sum+=$df;
                }
            }
            if ($debug) $res->dbg.="\nChanges: ".print_r($changes, true);
            // log stability 
            // write_log(print_r($years, true));
            // write_log(print_r($changes, true));
            
            if ($debug) $res->dbg.="n = $n   sum = $sum\n";
            
            if ($n!=0) $stability = $sum/$n;
            else $stability='NULL';
            //write_log("stability = $stability");
        }

        
        $prm->max_year = $yr->maxyear;  
        
        $sql = "select sum(t.sales) from (select d.sales from sales_divdetails d 
        join sales_companies c on  d.cid = c.cid
        where  d.sic=:sic and d.syear=:max_year $region $wsize order by 1 desc limit 3) t";
        $qr = $db->query($sql, $prm);
        $top3sum = $db->fetchSingleValue($qr);
        if (empty($top3sum)) $top3sum = '0';
        
        if ($debug)
        { $res->dbg.="\nSQL: $sql\n\n";
          $res->dbg.="Params: ".print_r($prm, true)."\n";
          $res->dbg.="top3sum: $top3sum\n";
        }
        
                

        $sql = "select sum(t.sales) from (select d.sales from sales_divdetails d 
        join sales_companies c on  d.cid = c.cid
        where  d.sic=:sic and d.syear=:max_year $region  $wsize order by 1 desc limit 5) t";
        $qr = $db->query($sql, $prm);
        $top5sum = $db->fetchSingleValue($qr);
        if (empty($top5sum)) $top5sum = '0';
        
        if ($debug)
        { $res->dbg.="\nSQL: $sql\n\n";
          $res->dbg.="Params: ".print_r($prm, true)."\n";
          $res->dbg.="top3sum: $top5sum\n";
        }        
        
        // get total sales of companies with selected SIC number and max year
        $sql = "select dd.cid, sum(sales) as ctotal
from sales_divdetails dd
where dd.syear=:max_year and dd.sales>0 and dd.cid in
(select 
    d.cid 
 from sales_divdetails d 
 join sales_companies c on  d.cid = c.cid
 where d.sic=:sic and d.syear=:max_year $region $wsize
)
group by dd.cid";
        $qr = $db->query($sql, $prm);
        $ctotal = array();
        while ($r = $db->fetchSingle($qr))
        {  $ctotal[$r->cid] = $r->ctotal;
        }
        if ($debug)
        { $res->dbg.="----- Get total sales of companies------\n";
          $res->dbg.="\nSQL: $sql\n\n";
          $res->dbg.="Params: ".print_r($prm, true)."\n";
          $res->dbg.="ctotal: ".print_r($ctotal, true)."\n";
        }        

       // write_log(print_r($ctotal, true));
       //  return $this->error("Stability = $stability", true); 

        $sql = "select 
    d.cid,c.sales,d.sales as dsales,c.market_cap,c.sales_growth,
    c.roic, c.pe, c.evebitda, c.payout, c.reviewed
from sales_divdetails d 
join sales_companies c on d.cid=c.cid 
where d.sic=:sic and d.syear=:max_year $region $wsize";
        $qr = $db->query($sql, $prm);
        $rows = array();
        $fin = new stdClass();
        $fin->ape = 0.0;
        $fin->asales_growth = 0.0;
        $fin->aroic = 0.0;
        $fin->aevebitda = 0.0;
        $fin->apayout = 0.0;
        $fin->market_cap =  0.0;
        $fin->tsales = 0.0;
        
        $proc_sum = 0.0;
        $rownum = 0;
        $reviewed = 0;
        /*
         * Company A generates 20% of sales from SIC 1111 and has PE of 12
 Company B generates 30% of sales from SIC 1111 and has PE of 15
 Company C generates 90% of sales from SIC 1111 and has PE of 20
 Therefore, weighted average PE is ((20% x 12) + (30% x 15) + (90% x 20)) / (20% + 30% + 90%) = 17.79. 
                                          This would work for Sales growth, ROIC, PE, EVEBITDA, Payout. 
 
 For MARKET CAP, we would just take the shares of market caps according to SIC exposure eg:
 Company A generates 20% of sales from SIC 1111 and has MKT CAP of 100
 Company B generates 30% of sales from SIC 1111 and has MKT CAP of 200
 Company C generates 90% of sales from SIC 1111 and has MKT CAP of 300
 Therefore, overall market cap would be ((20% x 100) + (30% x 200) + (90% x 300) = 350.
 Ie the market cap would be the sum of the individual companies’ 
 estimate market value attributable to that SIC ie 350, 
 whereas the other measures would be weighted average values. 
         */
        if ($debug)
        { $res->dbg.="----- Get companies data ------\n";
          $res->dbg.="\nSQL: $sql\n\n";
          $res->dbg.="Params: ".print_r($prm, true)."\n";
        } 
        
        $head = true;
        
        while ($r = $db->fetchSingle($qr))
        {  
           $ctot = 0;
           if (isset($ctotal[$r->cid])) $ctot=$ctotal[$r->cid];
           if ($ctot==0) $pr = NULL; else
           $pr = $r->pofsale = ($r->dsales / $ctot) * 100.0;           
           if ($debug) 
           {   if ($head) 
               { $res->dbg.=implode("\t",array_keys((array)$r))."\n";
                 $head = false;
               }
               $res->dbg.=implode("\t",(array)$r)."\n";
           }
           $fin->ape+= $pr * $r->pe;
           $fin->asales_growth += $pr * $r->sales_growth;
           $fin->aroic += $pr * $r->roic;
           $fin->aevebitda += $pr * $r->evebitda;
           $fin->apayout += $pr * $r->payout;
           if ($r->dsales>0) $fin->tsales += $r->dsales;
           $proc_sum+=$pr;
           $fin->market_cap += $pr * $r->market_cap;
                      
           $rows[]=$r;
           if ($r->reviewed) $reviewed++;
           $rownum++;
        }
        if ($rownum==0 || $proc_sum==0)
        {  $fin->ape = NULL;
            $fin->asales_growth = NULL;
            $fin->aroic = NULL;
            $fin->aevebitda = NULL;
            $fin->apayout = NULL;
        } else
        {   $fin->ape /= $proc_sum;
            $fin->asales_growth /= $proc_sum;
            $fin->aroic /= $proc_sum;
            $fin->aevebitda /= $proc_sum;
            $fin->apayout /= $proc_sum;
        }

        if ($debug)
        {  $res->dbg.="\nfin: ".print_r($fin, true)."\n";
        } 

        $fin->stability = $stability;
        $fin->top3sum = 1.0*$top3sum;
        $fin->top5sum = 1.0*$top5sum;
        $sql = "select name from sales_sic where id=:sic";
        $qr = $db->query($sql, array('sic'=>$prm->sic));
        $fin->name = $db->fetchSingleValue($qr);
        
        $fin->sic = $prm->sic;
        if ($rownum==0) $fin->previewed=NULL; 
        else $fin->previewed = ($reviewed/$rownum)*100;
        
       $res->rows = array($fin);
       return $res;       
   }

   function ajxMarketSummarySic()
   {  // Min Size not used now
      $db = $this->cfg->db;
      $db->query('call select_single_sic(:sic)', $this->getPostParams('sic') );
      $db->query('select max(syear) from sales_divdetails into @max_year');
      $qr = $db->query('call summary_by_sics(@max_year,:sic,:region);', $this->getPostParams('sic,region') );
      $this->res->rows = $qr->fetchAll(PDO::FETCH_OBJ);
      $qr->closeCursor();        
      // $this->res->rows = $r->rows;
      // if (isset($r->dbg)) $this->res->dbg = $r->dbg;
      echo json_encode($this->res);        
   }

   function getPostParams($list)
   { $r = array();
      $a = explode(',', $list);
      foreach($a as $v) 
      { if (isset($_POST[$v])) $r[$v] = $_POST[$v];
      }
      return $r;
   }

   function selectSicsByThemeRange($db)
   {    $db = $this->cfg->db; 
        $db->query('set @theme_min = :theme_min', $this->getPostParams('theme_min') );
        $db->query('set @theme_max = :theme_max', $this->getPostParams('theme_max') );
        $db->query('set @theme_id = :theme_id', $this->getPostParams('theme_id') );
        $db->query('CREATE TEMPORARY TABLE tmp_selected_sics (sic integer NOT NULL)');
        $db->query('insert into tmp_selected_sics
select id
from sales_sic 
where CSV_DOUBLE(exposure,@theme_id)  between @theme_min and @theme_max
and id<>9999;');

       // id in (116,119,131) 
   }


   // Sum divided by sum  calculation
   function prepareSumBySumCalcBySics($f1, $f2, $year='NULL', $all_sics=false)
   {  $db = $this->cfg->db;
      $db->query('DROP TABLE IF EXISTS tmp_ebit_sum_by_cid_sic_year');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_ebit_sum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null)');
      $field = $f1;
      
      $tmpjoin = ' join tmp_selected_sics ss on d.sic=ss.sic';
      if ($all_sics) $tmpjoin = '';
      
      $db->query("insert into tmp_ebit_sum_by_cid_sic_year 
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.$field)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   $tmpjoin
where  d.$field is not null
   and (:region='' or :region='Global' or c.region=:region)
   and ($year is NULL or d.syear=$year)
group by d.syear, d.cid, d.sic
having sum(d.$field)>0", $this->getPostParams('region'));

      $db->query('DROP TABLE IF EXISTS tmp_asset_sum_by_cid_sic_year');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_asset_sum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null)');
      $field = $f2;
      $db->query("insert into tmp_asset_sum_by_cid_sic_year 
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.$field)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   $tmpjoin
where  d.$field is not null
   and (:region='' or :region='Global' or c.region=:region)
   and ($year is NULL or d.syear=$year)
group by d.syear, d.cid, d.sic
having sum(d.$field)>0", $this->getPostParams('region'));
     $db->query('DROP TABLE IF EXISTS tmp_values_by_sic_year');
          $db->query('CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_values_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null)');


    $db->query('DROP TABLE IF EXISTS tmp_values_by_sic_year');
          $db->query('CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_values_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null)');
    
    $db->query("insert into tmp_values_by_sic_year
select 
    e.syear,
    e.sic,
    100.0*sum(e.v)/sum(a.v) as v
from  tmp_ebit_sum_by_cid_sic_year as e
join  tmp_asset_sum_by_cid_sic_year as a
    on e.sic=a.sic and e.cid=a.cid and e.syear=a.syear
group by 1,2
order by 2, 1 desc", $this->getPostParams('region'));

   }

    // field can takes values: ebit sales capex assets 
   function prepareGrowthsCalcBySics($field, $year=null, $all_sics=false)
   {  $db = $this->cfg->db;
      $db->query('DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null)');
      $wyear = '';
      
      $tmpjoin = ' join tmp_selected_sics ss on d.sic=ss.sic';
      if ($all_sics) $tmpjoin = '';
      
      if ($year!=null) $wyear="and (d.syear=$year or d.syear=$year-1)";
      $db->query("insert into tmp_vsum_by_cid_sic_year
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.$field)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   $tmpjoin
where  d.$field is not null
   and (:region='' or :region='Global' or c.region=:region)
   $wyear
group by d.syear, d.cid, d.sic
having sum(d.$field)>0", $this->getPostParams('region'));

    $db->query('DROP TABLE IF EXISTS tmp_values_by_sic_year');
          $db->query('CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_values_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null)');
 
    $wyear = '';
    if ($year!=null) $wyear="and (d.syear=$year)";
    
    $db->query("insert into tmp_values_by_sic_year
select 
    r2.syear,
    r2.sic,
    sum(gv*r2.v)/sum(r2.v) as v
from
(   select 
      r.syear,
      r.cid,
      r.sic,
      r.v,
      100*(r.v/t.v-1) as gv
    from
    (select 
        d.syear,
        d.cid,
        d.sic,
        sum(d.$field) as v    
    from sales_divdetails d
       join sales_companies c on  d.cid = c.cid
       $tmpjoin
    where  d.$field is not null
        and (:region='' or :region='Global' or c.region=:region)    
        $wyear
    group by d.syear, d.cid, d.sic
    having sum(d.$field)>0
    ) as r
    join tmp_vsum_by_cid_sic_year t 
        on t.syear=r.syear-1 
        and t.cid=r.cid
        and t.sic=r.sic
) as r2
group by 1,2
order by 2, 1 desc", $this->getPostParams('region'));       
   }
   
   // field can takes values: ebit sales capex assets 
   function prepareNyrGrowthsCalcBySics($field, $n, $year='NULL', $all_sics=false)
   {  $db = $this->cfg->db;
      $db->query('DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year');
      
      $tmpjoin = ' join tmp_selected_sics ss on d.sic=ss.sic';
      if ($all_sics) $tmpjoin = '';
      
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null)');
      $db->query("insert into tmp_vsum_by_cid_sic_year
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.$field)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   $tmpjoin
where  d.$field is not null
   and (:region='' or :region='Global' or c.region=:region)
   and ($year is NULL or d.syear=$year or d.syear=$year-$n)
group by d.syear, d.cid, d.sic
having sum(d.$field)>0", $this->getPostParams('region'));

      $db->query('DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year2');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year2 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null,gv double)');
    
    // green area
    $qr = $db->query("select 
      r.syear,
      r.cid,
      r.sic,
      r.v as v1,
      t.v as v2
    from
    (select 
        d.syear,
        d.cid,
        d.sic,
        sum(d.$field) as v    
    from sales_divdetails d
       join sales_companies c on  d.cid = c.cid
       $tmpjoin
    where  d.$field is not null
        and (:region='' or :region='Global' or c.region=:region)
        and ($year is NULL or d.syear=$year)
    group by d.syear, d.cid, d.sic
    having sum(d.$field)>0
    ) as r
    join tmp_vsum_by_cid_sic_year t 
        on t.syear=r.syear-$n 
        and t.cid=r.cid
        and t.sic=r.sic", $this->getPostParams('region'));       
        while ($r=$db->fetchSingle($qr))
        {   $v = null;
            if ($r->v2!=0.0)
            {   $v = 100*(pow($r->v1/$r->v2, 1.0/$n)-1);
                if (is_nan($v)) $v=null;
            } else $v = null;
            $v1 = $r->v1;
            unset($r->v1);
            unset($r->v2);
            $r->v = $v1;
            $r->gv = $v;
            $db->query('insert into tmp_vsum_by_cid_sic_year2 values (:syear,:cid,:sic,:v,:gv)', $r);
        }
        
        $db->query('DROP TABLE IF EXISTS tmp_values_by_sic_year');
        $db->query('CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_values_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null)');
    
        $db->query("insert into tmp_values_by_sic_year
select 
    r2.syear,
    r2.sic,
    sum(gv*r2.v)/sum(r2.v) as v
from tmp_vsum_by_cid_sic_year2 r2
group by 1,2
order by 2, 1 desc", $this->getPostParams('region'));
   }

   // field can takes values: ebit sales capex assets 
   function prepare3yrGrowthsCalcBySics($field, $year='NULL')
   {  $db = $this->cfg->db;
      $db->query('DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null)');
      $db->query("insert into tmp_vsum_by_cid_sic_year
select 
    d.syear,
    d.cid,
    d.sic,
    sum(d.$field)
from sales_divdetails d
   join sales_companies c on  d.cid = c.cid
   join tmp_selected_sics ss on d.sic=ss.sic
where  d.$field is not null
   and (:region='' or :region='Global' or c.region=:region)
   and ($year is NULL or d.syear=$year or d.syear=$year-3)
group by d.syear, d.cid, d.sic
having sum(d.$field)>0", $this->getPostParams('region'));

      $db->query('DROP TABLE IF EXISTS tmp_vsum_by_cid_sic_year2');
      $db->query('CREATE TEMPORARY TABLE
IF NOT EXISTS tmp_vsum_by_cid_sic_year2 
(syear integer not null, cid varchar(16) NOT NULL, sic integer NOT NULL,
v double not null,gv double)');
    
    // green area
    $qr = $db->query("select 
      r.syear,
      r.cid,
      r.sic,
      r.v as v1,
      t.v as v2
    from
    (select 
        d.syear,
        d.cid,
        d.sic,
        sum(d.$field) as v    
    from sales_divdetails d
       join sales_companies c on  d.cid = c.cid
       join tmp_selected_sics ss on d.sic=ss.sic
    where  d.$field is not null
        and (:region='' or :region='Global' or c.region=:region)
        and ($year is NULL or d.syear=$year)
    group by d.syear, d.cid, d.sic
    having sum(d.$field)>0
    ) as r
    join tmp_vsum_by_cid_sic_year t 
        on t.syear=r.syear-3 
        and t.cid=r.cid
        and t.sic=r.sic", $this->getPostParams('region'));       
        while ($r=$db->fetchSingle($qr))
        {   $v = null;
            if ($r->v2!=0.0)
            {   $v = 100*(pow($r->v1/$r->v2, 1.0/3.0)-1);
                if (is_nan($v)) $v=null;
            } else $v = null;
            $v1 = $r->v1;
            unset($r->v1);
            unset($r->v2);
            $r->v = $v1;
            $r->gv = $v;
            $db->query('insert into tmp_vsum_by_cid_sic_year2 values (:syear,:cid,:sic,:v,:gv)', $r);
        }
        
        $db->query('DROP TABLE IF EXISTS tmp_values_by_sic_year');
        $db->query('CREATE TEMPORARY TABLE
    IF NOT EXISTS tmp_values_by_sic_year
    (syear integer not null, sic integer NOT NULL,
    v double not null)');
    
        $db->query("insert into tmp_values_by_sic_year
select 
    r2.syear,
    r2.sic,
    sum(gv*r2.v)/sum(r2.v) as v
from tmp_vsum_by_cid_sic_year2 r2
group by 1,2
order by 2, 1 desc", $this->getPostParams('region'));
   }  
   
   function ajxTest3()
   {  $db = $this->cfg->db;
      $_POST['region']='';
      $db->query('SET @@sql_mode = "ONLY_FULL_GROUP_BY"');
      $db->query('call selectCustomSics()');
      
      // $this->prepare3yrGrowthsCalcBySics('capex');
      $this->calc5yrBySics('capex');      
      // $this->res->rows = $qr->fetchAll(PDO::FETCH_OBJ);
      echo json_encode($this->res);      
   }
   
   // final calculation for theams
   function aggregateBySics($all_sics=false)
   {  $db = $this->cfg->db;
      
      $tmpjoin = ' join tmp_selected_sics ss on d.sic=ss.sic';
      if ($all_sics) $tmpjoin = '';
      
      $qr = $db->query("select 
  r.syear,
  sum(t.v*r.tsales)/sum(r.tsales) as v
from
( select 
  d.syear,
  d.sic,
  sum(d.sales) as tsales
  from sales_divdetails d
     join sales_companies c on  d.cid = c.cid
     $tmpjoin 
  where  (:region='' or :region='Global' or c.region=:region)
  group by 1,2
) as r
join tmp_values_by_sic_year t on r.sic=t.sic and r.syear=t.syear
group by r.syear", $this->getPostParams('region'));
      return $qr->fetchAll(PDO::FETCH_OBJ);
   }
   
   function growthCalculation($hs)
   {  if (strpos($hs,'grw')===0)
      {  $db = $this->cfg->db;
         $f =  substr($hs,3); // ebit sales capex assets  
         $this->prepareGrowthsCalcBySics($f);
         return $this->aggregateBySics();
      }
      return false;       
   }

   // Calculation of 3yr EBIT growth, 3yr Sales growth e.t.c
   // If function name start with y3 then we caclulate values ant return array
   // else the function returns FALSE
   function growth3yrCalculation($hs)
   {  if (strpos($hs,'y3')===0)
      {  $db = $this->cfg->db;
         $f =  substr($hs,2); // ebit sales capex assets  
         $this->prepare3yrGrowthsCalcBySics($f);
         return $this->aggregateBySics();
      }
      return false;       
   }


   function growth5yrCalculation($hs)
   {  if (strpos($hs,'y5')===0)
      {  $db = $this->cfg->db;
         $f =  substr($hs,2); // ebit sales capex assets  
         
         $this->prepareNyrGrowthsCalcBySics($f, 5);
/*
         $qr = $db->query('select * from tmp_vsum_by_cid_sic_year2 order by syear');
         $this->res->green = $qr->fetchAll(PDO::FETCH_OBJ);
*/
         return $this->aggregateBySics();
      }
      return false;       
   }
   
   // for example if we have ebit-by-assets then we will get
   // sum of ebit divided by sob of assets
   function SumBySumCalculation($hs)
   {  if (strpos($hs,'-by-')>0)
      {  $p = explode('-by-',$hs);
         $db = $this->cfg->db;
         $this->prepareSumBySumCalcBySics($p[0], $p[1]);
         return $this->aggregateBySics();
      }
      return false;
   }

   function ajxThemesSummarySicTotals()
   {    $params = (object)$_POST;
        $db = $this->cfg->db;

        $db->query('call select_sics_by_themes(:theme_id,:theme_min,:theme_max)', 
          $this->getPostParams('theme_min,theme_max,theme_id'));
        
        if ( ($this->res->lrows=$this->growth3yrCalculation(post('lhs')))===false &&
             ($this->res->lrows=$this->growthCalculation(post('lhs')))===false &&
             ($this->res->lrows=$this->SumBySumCalculation(post('lhs')))===false
           )
        {   $qr = $db->query('call summary_by_sics_by_years(:lhs,:region)',  
              $this->getPostParams('lhs,region'));
            
            $this->res->lrows = $qr->fetchAll(PDO::FETCH_OBJ);
            $qr->closeCursor();
        }

        if (post('lhs')==post('rgs')) $this->res->rrows = $this->res->lrows;
        else 
        {   if ( ($this->res->rrows=$this->growth3yrCalculation(post('rhs')))===false &&
                 ($this->res->rrows=$this->growthCalculation(post('rhs')))===false &&
                 ($this->res->rrows=$this->SumBySumCalculation(post('rhs')))===false
            )
            {
                $qr2 = $db->query('call summary_by_sics_by_years(:rhs,:region)',  
                $this->getPostParams('rhs,region'));
                $this->res->rrows = $qr2->fetchAll(PDO::FETCH_OBJ);
                $qr2->closeCursor();
            }
        }
        echo json_encode($this->res);
   }
                      
   function ajxThemesSummary()
   {  $params = (object)$_POST;
      $db = $this->cfg->db;
      $db->query('select max(syear) from sales_divdetails into @max_year');
      
      $db->query('call select_sics_by_theme_range(@max_year,:theme_id,:theme_min,:theme_max,:region)', 
        $this->getPostParams('theme_min,theme_max,theme_id,region'));
        
      $qr = $db->query('call summary_by_sics(@max_year,:theme_id,:region)',
        $this->getPostParams('theme_id,region'));
        
      $this->res->rows = $qr->fetchAll(PDO::FETCH_OBJ);

      echo json_encode($this->res);
   }

    function calcSICstoSubsector($table, $f)
    {   $db = $this->cfg->db;     
        $qr = $db->query("select sum($f*t.psale) as v from $table st
join tmp_total_sic_subsector_psales t on st.sic=t.sic and t.subsector=:subsector",
            $this->getPostParams('subsector') );        
        return $db->fetchSingleValue($qr);
    }

    // result is temporary table: tmp_total_sic_tsales_by_years 
    function calcAllTopN($n)
    {   $db = $this->cfg->db;     
        $qr = $db->query("call get_topN_by_sic_years($n,@max_year,:region)",
             $this->getPostParams('region') );
    }

    // ROIC, PE, evebitda, payout   market_cap
    function calcSubsectorValues()
    {  $db = $this->cfg->db;     
       $qr = $db->query("
select
  st.syear,
  :subsector as name,
-- st.sic,
sum(st.tsales*t.psale) as tsales,  
sum(st.roic*t.psale) as aroic,
sum(st.pe*t.psale) as ape,
sum(st.evebitda*t.psale) as aevebitda,
sum(st.payout*t.psale) as apayout
from
(    select 
      d.syear,
      p.sic,
      sum(d.sales) as tsales,
      sum(c.roic*p.psale*t.sales)/sum(p.psale*t.sales) as roic,
      sum(c.pe*p.psale*t.sales)/sum(p.psale*t.sales) as pe,
      sum(c.evebitda*p.psale*t.sales)/sum(p.psale*t.sales) as evebitda,
      sum(c.payout*p.psale*t.sales)/sum(p.psale*t.sales) as payout            
    from sales_divdetails d
    join tmp_selected_sics ss on d.sic=ss.sic
    join sales_companies c on  d.cid = c.cid    
    join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
    join sales_companies_totals t on d.cid=t.cid
    where d.sales>0
     and (:region='' or :region='Global' or c.region=:region) 
     and (d.syear=@max_year)
    group by d.syear, d.sic
) as st
join tmp_total_sic_subsector_psales t on st.sic=t.sic and t.subsector=:subsector
group by 1,2", 
          $this->getPostParams('region,subsector'));
       return $qr->fetchAll(PDO::FETCH_OBJ);
    }

    function ajxMarketSummarySubsector()
    { // Min Size not used now
      $db = $this->cfg->db;
      
      // select sics of the subsector
      $db->query('select max(syear) from sales_divdetails into @max_year');
      $db->query('DROP TABLE IF EXISTS tmp_selected_sics');
      $db->query('CREATE TEMPORARY TABLE IF NOT EXISTS tmp_selected_sics (sic integer NOT NULL)');
      $db->query("insert into tmp_selected_sics
      select
     d.sic
from sales_divdetails d
join sales_companies c on d.cid=c.cid
where d.syear=@max_year and d.sales>0
and c.subsector=:subsector
and (:region='' or :region='Global' or c.region=:region)
group by 1", $this->getPostParams('subsector,region') );

      $this->tmpSubsectorPsales('@max_year');
      $this->res->rows = $this->calcSubsectorValues();
      
      if (isset($this->res->rows[0]))
      {  
         $this->calcAllTopN(3);
         $this->res->rows[0]->top3sum = 1*$this->calcSICstoSubsector('tmp_totalN_by_sic_years','proc');
         
         $this->calcAllTopN(5);
         $this->res->rows[0]->top5sum = 1*$this->calcSICstoSubsector('tmp_totalN_by_sic_years','proc');
         
         // stabilities 
         $qr = $db->query("call get_sics_stabilities(@max_year,:region)",
             $this->getPostParams('region') );
         $this->res->rows[0]->stability = 1.0*$this->calcSICstoSubsector('tmp_stabilities','stability');

         // sales growth
         $qr = $db->query("call sales_growth_by_year(@max_year,:region)",
             $this->getPostParams('region') );
         $this->res->rows[0]->asales_growth = 1.0*$this->calcSICstoSubsector('tmp_sales_growth_by_sic_year','v');
     
         // prewieved
         $qr = $db->query("select 
       100*sum(ct.reviewed)/count(*) as prewiewed
    from 
    (  select 
        d.cid, c.reviewed
        from sales_divdetails d
        join sales_companies c on  d.cid = c.cid
        join tmp_selected_sics ss on d.sic=ss.sic
        where d.syear=@max_year and c.subsector=:subsector
         and (:region='' or :region='Global' or c.region=:region)
        group by 1,2
    ) as ct", $this->getPostParams('subsector,region') );
         
        $this->res->rows[0]->previewed = 1*$db->fetchSingleValue($qr);
                  
      }
      echo json_encode($this->res); 
    }

    function getExposuresByPortfolio($ha, $comp_list=false)
    {  $db = $this->cfg->db;
                
        $sql = "CREATE TEMPORARY TABLE tmp_cids (cid varchar(16) NOT NULL, isin varchar(32),
 reviewed boolean, primary key (cid), index(isin))  ENGINE=MEMORY;";
        $db->query($sql);
        
        
        // saving not reviewed rows -------
        $sql = "insert into tmp_cids
select 
    c.cid, c.isin, false
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where not c.reviewed
group by 1,2;";
        $db->query($sql);
        
        
        //-- saving reviewed rows ------
        $sql = "insert into tmp_cids
select 
    c.cid, c.isin, true
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_portfolio_data p on c.isin = p.isin and p.portfolio_id=@pf
where c.reviewed
group by 1,2;";
        $db->query($sql);


        // calculate adjucted ------
        $sql = "select sum(p.val)
from tmp_cids t
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
into @pfsum;";
        $db->query($sql);
      
        $sql = "CREATE TEMPORARY TABLE tmp_fin_portfolio_values ( isin varchar(32),reviewed boolean, adjucted double ";
        foreach($ha as $k=>$v) $sql.=', p'.($k+1).' double ';
        $sql .= ", index(isin)) ENGINE=MEMORY;";
        $db->query($sql);

        
        // insert not reviewed values ---
        $sql = "insert into tmp_fin_portfolio_values
select t.isin, c.reviewed, p.val/@pfsum as adjucted ";
        foreach($ha as $k=>$v) $sql.=', sv.p'.($k+1).' ';
        $sql.="from tmp_cids t
join sales_companies c on t.cid=c.cid and not c.reviewed
join tmp_subsector_values sv on c.subsector = sv.subsector
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf;";
        $db->query($sql);
         
         $r = new stdClass();
         
        // If needed company list 
        if ($comp_list)
        {   $qr = $db->query('select c.name, c.subsector, c.cid, t.* from tmp_fin_portfolio_values t join sales_companies c on t.isin=c.isin');
            $r->clist = $qr->fetchAll(PDO::FETCH_OBJ);            
        }

        
        // Calculate total sales for each companie
        $sql = "CREATE TEMPORARY TABLE tmp_companie_total_sales ( cid varchar(16), total double, index(cid)) ENGINE=MEMORY;";
        $db->query($sql);

        $sql = "insert into tmp_companie_total_sales
select 
  d.cid, 
  sum(d.sales) as total
from tmp_cids t
join sales_divdetails d on d.cid=t.cid and t.reviewed and d.syear=@year and d.sales is not null
group by 1;";
        $db->query($sql);

        
        $sql = "insert into tmp_fin_portfolio_values
select t.isin, true as reviewed, p.val/@pfsum as adjucted";
        foreach($ha as $k=>$v) $sql.=',sum( d.sales*CSV_DOUBLE(s.exposure,'.($k+1).') ) / ts.total as t'.($k+1).' ';
        $sql .= "from tmp_cids t
join sales_divdetails d on t.cid=d.cid
join sales_sic s on d.sic=s.id
join tmp_companie_total_sales ts on t.cid = ts.cid
join sales_portfolio_data p on t.isin = p.isin and portfolio_id=@pf
where t.reviewed and d.syear=@year and d.sales is not null
group by 1,2,3;";
        $db->query($sql);

        
        $sql = "select ";
        foreach($ha as $k=>$v) $sql.='sum(t.p'.($k+1).'*t.adjucted) as s'.($k+1).', ';
        $sql .= "count(*) as count
from tmp_fin_portfolio_values t";
        $qr = $db->query($sql);  

        $d = $db->fetchSingle($qr);
        $a = array();
       
        foreach($ha as $k=>$v) 
        {   $s = 's'.($k+1);
            $a[$k] = $d->$s;
        }
        $r->data = $a;
        $r->count=$d->count;
        $sql = "drop table tmp_cids";
        $db->query($sql);
        $sql = "drop table tmp_companie_total_sales";
        $db->query($sql);
        $sql = "drop table tmp_fin_portfolio_values";
        $db->query($sql);
        
        return  $r;
    }
    
    function ajxComparePortfolio()
    {   $db = $this->cfg->db;
        $sql = "select max(d.syear) as maxyear
from sales_divdetails d";
        $qr = $db->query($sql);
        $yr = $db->fetchSingle($qr);
        $params = (object)$_POST;
        $debug = false;
        
        $sql = "select headers from sales_exposure";
        $qr = $db->query($sql);
        $ha = explode(';', $db->fetchSingleValue($qr));
        
        $db->query("set @year=:year;", array('year'=>$yr->maxyear));
        if ($debug) write_log("set @year=".$yr->maxyear);
        
        // ----- Calc subsector values ---------------------
        $sql = "CREATE TEMPORARY TABLE tmp_subsector_total (subsector  varchar(100), total double, index(subsector)) ENGINE=MEMORY;";
        $db->query($sql);
        
        $sql = "insert into tmp_subsector_total
select c.subsector, sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
group by 1;";        
        $db->query($sql);
        
        $sql = "CREATE TEMPORARY TABLE tmp_subsector_values (subsector  varchar(100), p1 double, p2 double, p3 double, p4 double, index(subsector)) ENGINE=MEMORY;";        
        $db->query($sql);
        if ($debug) write_log(__LINE__);
        
        $sql = "insert into tmp_subsector_values
select 
    c.subsector";
    foreach($ha as $k=>$v) $sql.=', sum(d.sales*CSV_DOUBLE(s.exposure,'.($k+1).'))/t.total as p'.($k+1)."\n";
    $sql.="from sales_companies c
join sales_divdetails d on c.cid = d.cid and d.syear=@year
join sales_sic s on d.sic=s.id
join tmp_subsector_total t on c.subsector=t.subsector
group by 1;";
       // write_log($sql);       
       if ($debug) write_log($sql);
       $db->query($sql);
       if ($debug) write_log(__LINE__);
       
        
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf1));        
        $this->res->data1 = $this->getExposuresByPortfolio($ha, true);
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf2));        
        $this->res->data2 = $this->getExposuresByPortfolio($ha);
        
        $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf1));
        $this->res->name1 = $db->fetchSingleValue($qr);

        $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf2));
        $this->res->name2 = $db->fetchSingleValue($qr);
        
        $this->res->header = $ha;
        echo json_encode($this->res); 


    }
    
    function getStackedPortfolio()
    {  $db = $this->cfg->db;
       $sql = "CREATE TEMPORARY TABLE tmp_portfolio_wg (col smallint, total double, primary key (col))  ENGINE=MEMORY";
       $db->query($sql);
  
       $sql = "insert into tmp_portfolio_wg
select m.col, sum(p.val) as total
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
where portfolio_id=@pf
group by 1";
       $db->query($sql); 
 
       
       $sql = "select m.col, c.name, sum(p.val*m.val)/t.total as s
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
join tmp_portfolio_wg t on m.col=t.col
join sales_metrics_columns c on c.metric_id=@mt and m.col=c.col
where portfolio_id=@pf
group by 1,2
order by 1";
       $qr = $db->query($sql);
       
       $r = new stdClass();
       $r->data = array();
       $r->names = array();
       while ($row=$db->fetchSingle($qr))
       {   $i = $row->col-1;
           $r->data[$i] = $row->s;
           $r->names[$i] = $row->name;
       }
       
       $db->query('drop table tmp_portfolio_wg');
       return $r;
    }
    
    function ajxStackedChart()
    {   $db = $this->cfg->db; 
        $params = (object)$_POST;
         
        $db->query("set @mt=:mt;", array('mt'=>$params->mt)); 
        
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf1)); 
        $this->res->p1 = $this->getStackedPortfolio();

        $qr = $db->query("select 
    p.isin, p.val as weight, m.col, m.val, c.name
from sales_portfolio_data p
join sales_metrics_data m on p.isin=m.isin and m.metric_id=@mt
join sales_companies c on p.isin=c.isin 
where portfolio_id=@pf
order by p.isin, m.col");
        $this->res->chart2 = $qr->fetchAll(PDO::FETCH_OBJ);
        
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf2)); 
        $this->res->p2 = $this->getStackedPortfolio();
         
        $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf1));
        $this->res->name1 = $db->fetchSingleValue($qr);

        $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf2));
        $this->res->name2 = $db->fetchSingleValue($qr);
        
        $qr = $db->query("select metric from sales_metrics where id=:id", array('id'=>$params->mt));
        $this->res->metric = $db->fetchSingleValue($qr);
        
        echo json_encode($this->res); 
    }
    
    function ajxMetricsAnalysis()
    {   $db = $this->cfg->db; 
        $params = (object)$_POST;
        $res = array();
        if (isset($params->rows))
        foreach ($params->rows as $a)
        {   $r = (object)$a;
            $db->query("set @mt=:mt;", array('mt'=>$r->id)); 
            $db->query("set @pf=:pf;", array('pf'=>$params->p));
            $qr = $db->query("select metric from sales_metrics where id=:id", array('id'=>$r->id));
            $r->name = $db->fetchSingleValue($qr);
            $r->p = $this->getStackedPortfolio();            
            $db->query("set @pf=:pf;", array('pf'=>$params->c));
            $r->c = $this->getStackedPortfolio();
            unset($r->c->names);
            $res[] = $r;
        }
        $this->res->rows = $res;
        echo json_encode($this->res); 
    }
    
    
    function ajxStackedChartList()
    {   $db = $this->cfg->db; 
        $params = (object)$_POST;
        $db->query("set @mt=:mt;", array('mt'=>$params->mt)); 
        //$db->query("set @pf=:pf;", array('pf'=>$params->pf)); 
        
        
    }
    
    function ajxSectorAllocChart()
    {   
        
        function average($a)
        { $s = 0;
          if (count($a)==0) return NULL;
          foreach($a as $v) $s+=$v;
          return $s/count($a);
        }
        
        function sum($a)
        { if (count($a)==0) return NULL;
          $s = 0;
          foreach($a as $v) $s+=$v;
          return $s;
        }
            
        function TRIMMEAN($aArgs, $percent) 
        {
            if ((is_numeric($percent)) && (!is_string($percent))) 
            {   if (($percent < 0) || ($percent > 1)) return false;       
                $mArgs = array();
                foreach ($aArgs as $arg) {
                  // Is it a numeric value?
                  if ((is_numeric($arg)) && (!is_string($arg))) {
                    $mArgs [] = $arg;
                  }
                }
                $discard = floor(count($mArgs) * $percent / 2);
                sort($mArgs);
                for ($i = 0; $i < $discard; ++$i) {
                  array_pop($mArgs);
                  array_shift($mArgs);
                }
                return average($mArgs);
          }
          return PHPExcel_Calculation_Functions::VALUE();
        }
        // -- Sector Alloc code
        $db = $this->cfg->db; 
        $params = (object)$_POST;         
        $db->query("set @mt=:mt;", array('mt'=>$params->mt)); 
        $db->query("set @pf=:pf;", array('pf'=>$params->pf1)); 
        
        $qr = $db->query("select d.col, d.val, c.sector
from sales_metrics_data d
join sales_companies c on d.isin = c.isin
where metric_id=@mt");

        $a = array();        
        
        // fill data for the trimmean calculations
        while ($r=$db->fetchSingle($qr))
        {   $sector = $r->sector;
            $col = $r->col;
            if (!isset($a[$sector])) $a[$sector] = array();
            if (!isset($a[$sector][$col])) $a[$sector][$col] = array();
            $a[$sector][$col][] = 1.0*$r->val;
        }
        
        // param $a - source data of sector values from metrics
        function calcTotals($db, $a)
        {  // select data for portfolio 1
            $qr = $db->query("select p.isin, p.val, c.sector, sum(d.val) as tmetric
from sales_portfolio_data p
join sales_companies c on p.isin = c.isin
join sales_metrics_data d on p.isin=d.isin and d.metric_id=@mt
where p.portfolio_id=@pf
group by 1");
         
            $trimm = array();
            $res = new stdClass();
            
            $res->actual = 0;
            $res->sector_av = 0;
            $total_weights = 0;
            
            $rows = array();
            while ($r=$db->fetchSingle($qr))
            { $rows[] = $r;
              $total_weights+=1.0*$r->val;
            }
            
            $kk = 1.0/$total_weights;
            
            foreach($rows as $r)
            { $sector = $r->sector;
              if (!isset($trimm[$sector]) && isset($a[$sector]))
              {   $trimm[$sector]=array();
                  foreach($a[$sector] as $k=>$v)
                  {  // TRIMMEAN calculations
                      $trimm[$sector][$k] = TRIMMEAN($v, 0.1);
                  }
              }        
              if (isset($trimm[$sector])) $r->trimm_total = sum( $trimm[$sector] );
              $res->actual+=(1.0*$r->tmetric*$r->val*$kk);
              $res->sector_av+=(1.0*$r->trimm_total*$r->val*$kk);
            }
            $res->k = $kk;
            $res->total = $total_weights;
            return $res;
        }
        
        $this->res->pf1 = calcTotals($db, $a);
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf2)); 
        $this->res->pf2 = calcTotals($db, $a);
        
        if ($this->res->pf1->actual < $this->res->pf2->actual)
        {   $this->res->reverse = false;
            $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf1));
            $this->res->xdata[] = array('name'=>$db->fetchSingleValue($qr), 'y'=>1.0*$this->res->pf1->actual);
            $sa = 1.0*$this->res->pf1->sector_av - $this->res->pf1->actual;
            $this->res->xdata[] = array('name'=>'Sector allocation', 'y'=>$sa);
            $ss = 1.0*$this->res->pf2->actual - $sa - $this->res->pf1->actual;
            $this->res->xdata[] = array('name'=>'Stock selection', 'y'=>$ss);
            $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf2));
            $this->res->xdata[] = array('name'=>$db->fetchSingleValue($qr), 'isSum'=>true);
        } else
        {  $this->res->reverse = true;
           $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf2));
           $this->res->xdata[] = array('name'=>$db->fetchSingleValue($qr), 'y'=>1.0*$this->res->pf2->actual);
           $sa = 1.0*$this->res->pf2->sector_av - $this->res->pf2->actual;
           $this->res->xdata[] = array('name'=>'Sector allocation', 'y'=>$sa);
           $ss = 1.0*$this->res->pf1->actual - $sa - $this->res->pf2->actual;
           $this->res->xdata[] = array('name'=>'Stock selection', 'y'=>$ss);
           $qr = $db->query("select portfolio from sales_portfolio where id=:pf", array('pf'=>$params->pf1));
           $this->res->xdata[] = array('name'=>$db->fetchSingleValue($qr), 'isSum'=>true);
        }
        
        echo json_encode($this->res); 
    }
    
    function ajxCompaniesAnalysis()
    {   $db = $this->cfg->db; 
        $params = (object)$_POST;  
        $titles = explode(';',';Total sales;Sales growth;ROIC;PE;EVBIDTA;Payout;% reviewed');
        $axis = array(null,'sales','sales_growth', 'roic', 'pe','evebitda', 'payout', 'reviewed');
        $flds = array();
        
        $wh = array();
        $wp = array();
        
        if (isset($axis[$params->xaxis]) && $axis[$params->xaxis]!=null) 
            $flds[]=$axis[$params->xaxis].' as x ';
        if (isset($axis[$params->yaxis]) && $axis[$params->yaxis]!=null) 
            $flds[]=$axis[$params->yaxis].' as y ';
        if ($params->mode=='Subsector' && isset($params->id))
        { $wh[] = 'subsector=:subsector';
          $wp['subsector'] = $params->id;
        }
        if ($params->mode=='SIC' && isset($params->id))
        { $wh[] = 'cid in (select d.cid from sales_divdetails d where d.sic=:sic and d.syear=@maxyear)';
          $wp['sic'] = $params->id;
        }
        if (isset($params->region) && $params->region!='Global')
        { $wh[] = 'region=:region';
          $wp['region'] = $params->region;
        }
        if (isset($params->min_size) && 1*$params->min_size > 0)
        {  $wh[] = 'sales>:minsize';
           $wp['minsize'] = $params->min_size;
        }
        
        
        if (count($flds)==2)
        {    $db->query('select max(syear), min(syear) from sales_divdetails into @maxyear, @minyear');
             $flds[]='name';
             $sql = "select ".implode(',',$flds).' from sales_companies ';
             if (count($wh)>0) $sql.=' where '.implode(' and ', $wh);
             $qr = $db->query($sql, $wp);
             $data = array();
             while ($r=$db->fetchSingle($qr)) 
             { $r->x *= 1.0;
               $r->y *= 1.0;
               $data[] = $r;
             }
             // $this->res->sql = $sql;
             $this->res->xdata = $data;
             $this->res->xtitle = $titles[$params->xaxis];
             $this->res->ytitle = $titles[$params->yaxis];
        }
        echo json_encode($this->res); 
    }
    
 
    function calcAllSicsStabilities()
    {  $db = $this->cfg->db; 
       $db->query('call get_all_sics_stabilities(@max_year, :region)',
            $this->getPostParams('region'));
            
       $qr = $db->query('select t.sic, t.stability as v '
       .' from tmp_stabilities t ');
       return $qr->fetchAll(PDO::FETCH_OBJ);
    }
 
   function getSicTotalsTmp($selected=true)
   {  $db = $this->cfg->db; 
      $db->query("DROP TABLE IF EXISTS tmp_total_sic_tsales_by_years");
      $db->query("CREATE TEMPORARY TABLE tmp_total_sic_tsales_by_years
        (syear integer, sic integer, tsum double, PRIMARY KEY (syear, sic))");
      $tmpsel = '';
      if ($selected) $tmpsel='join tmp_selected_sics ss on d.sic=ss.sic ';
      $db->query("insert into tmp_total_sic_tsales_by_years 
    select 
        d.syear, d.sic, sum(d.sales) as v
    from sales_divdetails d
      join sales_companies c on  d.cid = c.cid  
      $tmpsel    
    where 
        d.sales>0
        and (:region='' or :region='Global' or c.region=:region)
        and (@max_year is null or d.syear=@max_year)
   group by 1,2
   order by 1,2",
            $this->getPostParams('region'));  
   }
 
   function calcAllSicsTopN($n)
   {  $db = $this->cfg->db; 
      $db->query('set @gr=NULL');
      $db->query('set @sc=NULL');
      $db->query('set @n=0');
      $db->query('DROP TABLE IF EXISTS tmp_totalN_by_sic_years');
      $db->query('CREATE TEMPORARY TABLE tmp_totalN_by_sic_years
        (syear integer, sic integer, proc double, PRIMARY KEY (syear, sic))');
      $this->getSicTotalsTmp(false);      
    $db->query("insert into tmp_totalN_by_sic_years
    select r2.syear, r2.sic, sum(r2.tsum)*100/t.tsum as v
    from
    ( select
           @n:=if(@gr=r.syear and @sc=r.sic,@n+1,1) as rank,
           @gr:=r.syear as syear,
           @sc:=r.sic as sic,
           r.cid,
           r.tsum
        from
        (select     
           d.syear,
           d.sic,
           d.cid,
           sum(d.sales) as tsum
            from sales_divdetails d
            join sales_companies c on  d.cid=c.cid
        where d.sales>0 
            and (:region='' or :region='Global' or c.region=:region)
            and (@max_year is null or d.syear=@max_year)
        group by d.syear, d.sic, d.cid
        order by d.syear, d.sic, tsum desc
        ) as r
    ) as r2
        join tmp_total_sic_tsales_by_years  t 
            on r2.syear=t.syear and r2.sic=t.sic
    where rank<=$n
    group by r2.syear, r2.sic, t.tsum", 
        $this->getPostParams('region'));
     
     $qr = $db->query("select p.syear, p.sic,  sum(p.proc*t.tsum)/sum(t.tsum) as v        
    from tmp_totalN_by_sic_years p
    join tmp_total_sic_tsales_by_years t
        on p.syear=t.syear and p.sic=t.sic
    group by p.syear, p.sic"); 
     return $qr->fetchAll(PDO::FETCH_OBJ);               
   }
 
   function getTmpSicsValues()
   {  $db = $this->cfg->db; 
      $qr = $db->query("select * from tmp_values_by_sic_year"); 
     return $qr->fetchAll(PDO::FETCH_OBJ);  
   }
 
   function allSICsSumBySum($hs)
   {
      if (strpos($hs,'-by-')>0)
      {  $p = explode('-by-',$hs);
         $db = $this->cfg->db;
         $this->prepareSumBySumCalcBySics($p[0], $p[1], '@max_year', true);
         return $this->getTmpSicsValues();       
      }
      return false;
    }
 
   function allSICsNyrGrowth($hs, $n)
   {  if (strpos($hs,'y'.$n)===0)
      {  $db = $this->cfg->db;
         $f =  substr($hs,2); // ebit sales capex assets  
         $this->prepareNyrGrowthsCalcBySics($f,$n,'@max_year', true);
         return $this->getTmpSicsValues();     
      }
      return false;       
   }   
   
   function allSICsGrowth($hs)
   {  if (strpos($hs,'grw')===0)
      {  $db = $this->cfg->db;
         $f =  substr($hs,3); // ebit sales capex assets  
         $this->prepareGrowthsCalcBySics($f,'@max_year', true);         
         return $this->getTmpSicsValues();     
      }
      return false;       
      
   }
   
   function allBySICs($prepare=false)
   {   $params = (object)$_POST;
       $db = $this->cfg->db;
       $db->query('select max(syear) from sales_divdetails into @max_year');
       
       function calcByParam($ctx, $f)
       {   switch ($f)
           {   case 'tsales':
               case 'roic':
               case 'pe':
               case 'evebitda':
               case 'payout':
               case 'market_cap':
                  return $ctx->calcAllSicValues($f);
               break; 
               case 'top3':
                  return $ctx->calcAllSicsTopN(3);
               break;
               case 'top5':
                  return $ctx->calcAllSicsTopN(5);
               break;
               case 'stability':
                  return $ctx->calcAllSicsStabilities();
               break;
               default:
                 if (($r=$ctx->allSICsGrowth($f))!==false) return $r;
                 else if (($r=$ctx->allSICsNyrGrowth($f, 5))!==false) return $r;
                 else if (($r=$ctx->allSICsNyrGrowth($f, 3))!==false) return $r;
                 else if (($r=$ctx->allSICsSumBySum($f))!==false) return $r;
           }
           return array();
       }
       
       $data = array();
       $x = calcByParam($this, post('xaxis'));
       $y = calcByParam($this, post('yaxis'));
       
       foreach($x as $r)
       { $data[$r->sic] = new stdClass();
         $data[$r->sic]->x = 1.0*$r->v;   
         $data[$r->sic]->y =  null; 
       }
       foreach($y as $r)
       { if (!isset($data[$r->sic]))
         { $data[$r->sic] = new stdClass();
           $data[$r->sic]->x = null;   
         }          
         $data[$r->sic]->y = 1.0*$r->v;   
       }
       
       if (!$prepare)
       {
           $qr = $db->query("select
           s.id, s.name       
        from sales_sic s");
           
           while ($r = $db->fetchSingle($qr))
           {  if (isset($data[$r->id])) $data[$r->id]->name = $r->name;
           }
       }
              
       $xdata = array();
       foreach ($data as $k=>$r) 
       { $n = new stdClass();
         $n->id = $k;
         if (!$prepare) $n->name = $r->name;
         $n->x = $r->x;
         $n->y = $r->y;
         $xdata[] = $n;
       }
       if (!$prepare)
       {
         $this->res->xdata = $xdata;
         echo json_encode($this->res); 
       } else return $xdata;
    }
 

   // Calculation of the percent of psales
   // Result is in the temporary table: tmp_total_sic_subsector_psales
   function tmpSubsectorPsales($year)
   {  $db = $this->cfg->db;
      $db->query('DROP TABLE IF EXISTS tmp_total_subsector_sales');
      $db->query('CREATE TEMPORARY TABLE tmp_total_subsector_sales
    (subsector varchar(100), tsum double, PRIMARY KEY (subsector) )');    
      $db->query("insert into tmp_total_subsector_sales
select
     c.subsector, sum(d.sales)
from sales_divdetails d
join sales_companies c on d.cid=c.cid
where d.syear=$year and d.sales>0
group by 1");

      $db->query("DROP TABLE IF EXISTS tmp_total_sic_subsector_psales");
      $db->query("CREATE TEMPORARY TABLE tmp_total_sic_subsector_psales
(sic integer, subsector varchar(100), psale double, PRIMARY KEY (sic,subsector) )");

      $db->query("insert into tmp_total_sic_subsector_psales
select
      d.sic, c.subsector, sum(d.sales)/t.tsum
from sales_divdetails d
join sales_companies c on d.cid=c.cid
join tmp_total_subsector_sales t on c.subsector=t.subsector
where d.syear=$year and d.sales>0
group by 1,2,t.tsum");

   }

   function allBySubsector()
   {   $d = $this->allBySICs(true);
       $this->tmpSubsectorPsales('@max_year');
       $db = $this->cfg->db;
       $db->query('DROP TABLE IF EXISTS tmp_xdata');
       $db->query('CREATE TEMPORARY TABLE tmp_xdata
    (id integer not null, x double, y double,  PRIMARY KEY (id) )');    
       $q = $db->db->prepare('insert into tmp_xdata values (:id, :x, :y)');
       foreach($d as $r) $q->execute((array)$r);
       
       $qr = $db->query('select 
  p.subsector as name,
  sum(p.px) as x,
  sum(p.py) as y
  from
(select 
   t.sic, t.subsector, sum(t.psale*x.x) as px, sum(t.psale*x.y) as py 
from tmp_total_sic_subsector_psales t
join tmp_xdata x on t.sic = x.id
group by 1,2) as p
group by 1');
       
       $this->res->xdata = $qr->fetchAll(PDO::FETCH_OBJ);
       foreach ($this->res->xdata as $k=>$v)
       {   $this->res->xdata[$k]->x*=1.0;
           $this->res->xdata[$k]->y*=1.0;
       }
       echo json_encode($this->res);  
   }
 
    
 
    function ajxIndustryAnalysis()
    { $mode = post('mode');
      if ($mode==2) $this->allBySubsector();
      else $this->allBySICs();
      // else 
      // $this->error('Should be fixed', true);
    }
    
    function ajxThemesComparison()
    { $params = (object)$_POST;
      $db = $this->cfg->db;
      $db->query('select max(syear) from sales_divdetails into @max_year');
      // count number of theams
      $qr = $db->query('select headers from sales_exposure');
      $h = $db->fetchSingleValue($qr);
      $header = explode(';',$h);
      $n = count($header);
      $rows = array();
      if ($n>0)
      for ($i=1; $i<=$n; $i++)
      {   $_POST['theme_id'] = $i;
          $db->query('call select_sics_by_theme_range(@max_year,:theme_id,:theme_min,:theme_max,:region)', 
          $this->getPostParams('theme_min,theme_max,theme_id,region'));
          $qr = $db->query('call summary_by_sics(@max_year,:theme_id,:region)',
          $this->getPostParams('theme_id,region'));            
          $a = $qr->fetchAll(PDO::FETCH_OBJ);
          if (!empty($a))
          {   $a[0]->name = $header[$i-1];
              $rows[] = $a[0];
          }
          
      }
      $this->res->rows = $rows;
      echo json_encode($this->res); 
    }

    // $f could be: tsales, roic, pe, evebitda, payout
    function calcAllSicValues($f)
    {  $db = $this->cfg->db;
       if ($f=='tsales') $f='sales';
       if ($f=='sales') $st = 'sum(d.sales) as v ';
       else $st = " sum(c.$f*p.psale*t.sales)/sum(p.psale*t.sales) as v ";
       $qr = $db->query("select 
      d.syear,
      p.sic,
      $st
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
    join sales_companies_totals t on d.cid=t.cid
    where d.sales>0
     and (:region='' or :region='Global' or c.region=:region) 
     and (d.syear=@max_year)
    group by d.syear, d.sic", 
          $this->getPostParams('region'));
       return $qr->fetchAll(PDO::FETCH_OBJ);
    }
    
    // $f could be: tsales, roic, pe, evebitda, payout
    function calcSicValues($f)
    {  $db = $this->cfg->db;
       if ($f=='tsales') $f='sales';
       if ($f=='sales') $st = 'sum(d.sales) as v ';
       else $st = " sum(c.$f*p.psale*t.sales)/sum(p.psale*t.sales) as v ";
       $qr = $db->query("select 
      d.syear,
      p.sic,
      $st
    from sales_divdetails d
    join sales_companies c on  d.cid = c.cid
    join tmp_selected_sics ss on d.sic=ss.sic
    join sales_sic_companies_totals p on d.cid=p.cid and d.sic=p.sic
    join sales_companies_totals t on d.cid=t.cid
    where d.sales>0
     and (:region='' or :region='Global' or c.region=:region) 
     and (d.syear=@max_year)
    group by d.syear, d.sic", 
          $this->getPostParams('region'));
       return $qr->fetchAll(PDO::FETCH_OBJ);
    }
    
    function themesIndustryGrowth($hs)
    {  if (strpos($hs,'grw')!==0) return false;
       $f =  substr($hs,3);       
       $db = $this->cfg->db;
       $this->prepareGrowthsCalcBySics($f, '@max_year');
       $qr = $db->query("select * from tmp_values_by_sic_year");
       return $qr->fetchAll(PDO::FETCH_OBJ);        
    }
    
    function themesIndustrySumBySum($hs)
    {
      if (strpos($hs,'-by-')>0)
      {  $p = explode('-by-',$hs);
         $db = $this->cfg->db;
         $this->prepareSumBySumCalcBySics($p[0], $p[1], '@max_year');
         $db = $this->cfg->db;
         $qr = $db->query("select * from tmp_values_by_sic_year");
         return $qr->fetchAll(PDO::FETCH_OBJ);        
      }
      return false;
    }

    function themesIndustry3yrGrowth($hs)
    {  if (strpos($hs,'y3')!==0) return false;
       $f =  substr($hs,2);       
       $db = $this->cfg->db;
       $this->prepare3yrGrowthsCalcBySics($f, '@max_year');
       $qr = $db->query("select * from tmp_values_by_sic_year");
       return $qr->fetchAll(PDO::FETCH_OBJ);        
    }
        
    function themesIndustryTopN($n)
    { $db = $this->cfg->db;
      $db->query("call get_topN_by_sic_years($n,@max_year,:region)",
          $this->getPostParams('region'));
      $qr = $db->query("select syear, sic, proc as v from tmp_totalN_by_sic_years");
      return $qr->fetchAll(PDO::FETCH_OBJ);      
    }

    function themesIndustryStabilities()
    { $db = $this->cfg->db;
      $db->query("call get_sics_stabilities(@max_year,:region)",
           $this->getPostParams('region'));
      $qr = $db->query("select sic, stability as v from tmp_stabilities");
      return $qr->fetchAll(PDO::FETCH_OBJ);     
    }
        
    function ajxThematicIndustryComparison()
    {  $params = (object)$_POST;
       $db = $this->cfg->db;
       
       $db->query('call select_sics_by_themes(:theme_id,:theme_min,:theme_max)', 
                $this->getPostParams('theme_min,theme_max,theme_id'));
       $db->query('select max(syear) from sales_divdetails into @max_year');
       
       function calcByParam($ctx, $f)
       {   switch ($f)
           {   case 'tsales':
               case 'roic':
               case 'pe':
               case 'evebitda':
               case 'payout':
                  return $ctx->calcSicValues($f);
               break; 
               case 'top3':
                  return $ctx->themesIndustryTopN(3);
               break;
               case 'top5':
                  return $ctx->themesIndustryTopN(5);
               break;
               case 'stability':
                  return $ctx->themesIndustryStabilities();
               break;
               default:
                 if (($r=$ctx->themesIndustryGrowth($f))!==false) return $r;
                 else if (($r=$ctx->themesIndustry3yrGrowth($f))!==false) return $r;
                 else if (($r=$ctx->themesIndustrySumBySum($f))!==false) return $r;
           }
           return array();
       }
       
       $data = array();
       $x = calcByParam($this, post('xaxis'));
       $y = calcByParam($this, post('yaxis'));
               
       foreach($x as $r)
       { $data[$r->sic] = new stdClass();
         $data[$r->sic]->x = 1.0*$r->v;   
         $data[$r->sic]->y =  null; 
       }
       foreach($y as $r)
       { if (!isset($data[$r->sic]))
         { $data[$r->sic] = new stdClass();
           $data[$r->sic]->x = null;   
         }          
         $data[$r->sic]->y = 1.0*$r->v;   
       }
       
       $qr = $db->query("select
       s.id, s.name       
    from sales_sic s
    join tmp_selected_sics ss on s.id=ss.sic");
       
       while ($r = $db->fetchSingle($qr))
       {  if (isset($data[$r->id])) $data[$r->id]->name = $r->name;
       }
       
       
       $xdata = array();
       foreach ($data as $k=>$r) 
       { $n = new stdClass();
         $n->id = $k;
         $n->name = $r->name;
         $n->x = $r->x;
         $n->y = $r->y;
         $xdata[] = $n;
       }
       $this->res->xdata = $xdata;
       echo json_encode($this->res); 
    }


    // $f could be: sales, roic, pe, evebitda, payout, reviewed, market_cap
    function calcCompanyValues($f)
    {  $db = $this->cfg->db;
       /*
       if ($f=='tsales') $f='sales';
       if ($f=='sales') $st = 'sum(d.sales) as v ';
       else $st = " sum(c.$f*p.psale*t.sales)/sum(p.psale*t.sales) as v ";
       */
       $qr = $db->query("select
       c.cid, 
      c.$f as v
    from sales_companies c
    join tmp_selected_cids t on c.cid = t.cid    
    where  (:region='' or :region='Global' or c.region=:region)", 
          $this->getPostParams('region'));
       return $qr->fetchAll(PDO::FETCH_OBJ);
    }
    
    function ajxThematicCompanyComparison()
    {  $params = (object)$_POST;
       $db = $this->cfg->db;
       
       $db->query('select max(syear) from sales_divdetails into @max_year');
       
       $db->query('call select_companies_by_theme_range(@max_year, :theme_id,:theme_min,:theme_max,:region)', 
                $this->getPostParams('theme_min,theme_max,theme_id,region'));
       
       
       function calcByParam($ctx, $f)
       {   switch ($f)
           {   case 'sales':
               case 'roic':
               case 'pe':
               case 'evebitda':
               case 'market_cap':
               case 'reviewed':
               case 'payout':
                  return $ctx->calcCompanyValues($f);
               break; 
               /*
               case 'top3':
                  return $ctx->themesIndustryTopN(3);
               break;
               case 'top5':
                  return $ctx->themesIndustryTopN(5);
               break;
               case 'stability':
                  return $ctx->themesIndustryStabilities();
               break;
               default:
                 if (($r=$ctx->themesIndustryGrowth($f))!==false) return $r;
                 else if (($r=$ctx->themesIndustry3yrGrowth($f))!==false) return $r;
                 else if (($r=$ctx->themesIndustrySumBySum($f))!==false) return $r;
                 */
           }
           return array();
       }
       
       $data = array();
       $x = calcByParam($this, post('xaxis'));
       $y = calcByParam($this, post('yaxis'));
               
       foreach($x as $r)
       { $data[$r->cid] = new stdClass();
         $data[$r->cid]->x = 1.0*$r->v;   
         $data[$r->cid]->y =  null; 
       }
       foreach($y as $r)
       { if (!isset($data[$r->cid]))
         { $data[$r->cid] = new stdClass();
           $data[$r->cid]->x = null;   
         }          
         $data[$r->cid]->y = 1.0*$r->v;   
       }
       
       $qr = $db->query("select
       c.cid, c.name       
    from sales_companies c
    join tmp_selected_cids t on c.cid = t.cid");
       
       while ($r = $db->fetchSingle($qr))
       {  if (isset($data[$r->cid])) $data[$r->cid]->name = $r->name;
       }
       
       $xdata = array();
       foreach ($data as $k=>$r) 
       { $n = new stdClass();
         $n->id = $k;
         $n->name = $r->name;
         $n->x = $r->x;
         $n->y = $r->y;
         $xdata[] = $n;
       }
       
        
       $this->res->xdata = $xdata;
       echo json_encode($this->res); 
    }

    
    function ajxGetMaxYear()
    {   $db = $this->cfg->db;
        $qr = $db->query('select max(syear) as maxyear from sales_divdetails');
        $this->res->maxyear = $db->fetchSingleValue($qr); 
        echo json_encode($this->res); 
    }
    
    function ajxModelThemes()
    {   $db = $this->cfg->db;
        $qr = $db->query('select headers from sales_exposure');        
        $h = $db->fetchSingleValue($qr);
        $a = array();
        $r = explode(';',$h);
        $id=1;
        foreach($r as $v)
        {   $v=trim($v,' "');
            if (trim($v)!='') 
            { $a[] = (object)array('id'=>$id, 'name'=>$v);
              $id++;
            }
        }
        $this->res->rows = $a;
        echo json_encode($this->res);
    }
 
 }

?>
