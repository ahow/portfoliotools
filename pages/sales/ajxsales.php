<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include('../lib/params.php');
 // include('../lib/phpmailer.php');
 include('../lib/ajaxmodel.php');
 
 class ajxsales extends wAjaxModel
 {  
    function ajxModel()
    {   $this->includePageLocales(__DIR__);
        $this->processModel(__DIR__);
    }
    
    function beforeDivisionUpdate(&$row, $keys)
    {  //  $row->is_modified = 1;
        // $row->sex = 'M';        
        if (isset($this->cfg->user) && isset($this->cfg->user->user->id))
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
        $qr = $db->query('select headers from  sales_exposure');
        $a = explode(';', $db->fetchSingleValue($qr));
        $cols = array();
        foreach ($a as $k=>$v) $cols[]="e$k";
        $this->res->titles = array_merge($this->res->titles, $a);
        $this->res->columns = array_merge($this->res->columns, $cols);
        foreach($this->res->rows as $r)
        {  $d = explode(';', $r->exposure);
           foreach ($cols as $k=>$v) 
           {   $nv = $d[$k];
               if (1*$nv>0) $nv='+'.$nv;
               $r->$v = $nv;
           }
        }
    }
    
    function ajxForm1()
    {   $db = $this->cfg->db;
        $params = (object)$_POST;
        /*
        $qr = $db->query('select s.id, s.sales,d.division as number, ig.division as sic_division,
         ig.major_group, d.syear, d.me, d.sic, d.sales,sic.name as sicname, ig.industry_group, d.id as divdetail_id
         from sales_div_sales s
        join sales_divdetails d on s.id=d.div_sale_id
        join sales_sic sic on d.sic=sic.id
        join sales_industry_groups ig on sic.industry_group_id=ig.id
         where cid=:cid and d.syear<=:year order by d.division, d.syear',$params); */

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
        if (isset($params->sic) && isset($params->year))
        {  $prm->sic =  $params->sic;
           $prm->year_max = $params->year;
           $prm->year_min = $prm->year_max-2;
        } else return $this->error(T('SIC_OR_YEAR_NOT_FOUND'), __LINE__);
        
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

       $qr = $db->query($sql, $prm);
       $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
       echo json_encode($this->res);
    }
    
    function ajxMarketSummarySic()
   {   $params = (object)$_POST;
        $db = $this->cfg->db;
        $prm = new stdClass();
        $region = '';        
        $wsize = '';
        $region = '';
        
        $prm = new stdClass();
        
        if (isset($params->sic)) $prm->sic=$params->sic;
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
        
        
        // Stability calculations first we will get max and min year
        $sql = 
"select 
min(d.syear) as minyear, max(d.syear) as maxyear
from sales_divdetails d
join sales_companies c on  d.cid = c.cid
where d.sic=:sic $region $wsize";
        $qr = $db->query($sql, $prm);
        $yr = $db->fetchSingle($qr);
        
      
        
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
                while ( $r = $db->fetchSingle($qr) )
                {   $years[$y][$r->cid] = $i;
                    $i++;
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
            // log stability 
            // write_log(print_r($years, true));
            // write_log(print_r($changes, true));
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

        $sql = "select sum(t.sales) from (select d.sales from sales_divdetails d 
        join sales_companies c on  d.cid = c.cid
        where  d.sic=:sic and d.syear=:max_year $region  $wsize order by 1 desc limit 5) t";
        $qr = $db->query($sql, $prm);
        $top5sum = $db->fetchSingleValue($qr);
        if (empty($top5sum)) $top5sum = '0';
        
        
        // get total sales of companies with selected SIC number and max year
        $sql = "select dd.cid, sum(sales) as ctotal
from sales_divdetails dd
where dd.syear=:max_year and dd.cid in
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
        while ($r = $db->fetchSingle($qr))
        {  $pr = $r->pofsale = ($r->dsales / $ctotal[$r->cid]) * 100.0;
           $fin->ape+= $pr * $r->pe;
           $fin->asales_growth += $pr * $r->sales_growth;
           $fin->aroic += $pr * $r->roic;
           $fin->aevebitda += $pr * $r->evebitda;
           $fin->apayout += $pr * $r->payout;
           $fin->tsales += $r->dsales;
           $proc_sum+=$pr;
           $fin->market_cap += $pr * $r->market_cap;
                      
           $rows[]=$r;
           if ($r->reviewed) $reviewed++;
           $rownum++;
        }
        if ($rownum==0)
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
        $fin->stability = $stability;
        $fin->top3sum = $top3sum;
        $fin->top5sum = $top5sum;
        $sql = "select name from sales_sic where id=:sic";
        $qr = $db->query($sql, array('sic'=>$prm->sic));
        $fin->name = $db->fetchSingleValue($qr);
        
        $fin->sic = $prm->sic;
        if ($rownum==0) $fin->previewed=NULL; 
        else $fin->previewed = ($reviewed/$rownum)*100;
        
       $this->res->rows = array($fin);
       echo json_encode($this->res);
    }
 

    function ajxMarketSummarySubsector()
    {   $params = (object)$_POST;
        $db = $this->cfg->db;
        $prm = new stdClass();
        $region = '';        
        $wsize = '';
        $region = '';
        
        if (isset($params->subsector)) $prm->subsector=$params->subsector;
        
        if (isset($params->region)) 
        {  if ($params->region!='Global')
           {
               $region = '  and c.region=:region ';
               $prm->region =  $params->region;
           }
        }
        
        if (isset($params->min_size) && $params->min_size!='')
        {  $wsize = ' and c.sales>:min_size ';
           $prm->min_size = $params->min_size;
        }
        
        
        // Stability calculations first we will get max and min year
        $sql = 
"select 
min(d.syear) as minyear, max(d.syear) as maxyear
from sales_companies c
join sales_divdetails d on c.cid = d.cid
where c.subsector=:subsector $region $wsize";
        $qr = $db->query($sql, $prm);
        $yr = $db->fetchSingle($qr);
        
        // return $this->error("Stability OK", true);
        
        if (!empty($yr))
        {   $years = array();
            $yr->minyear=1*$yr->minyear;
            $yr->maxyear=1*$yr->maxyear;
            for ($y=$yr->minyear; $y<=$yr->maxyear; $y++)
            {   $sql = "select 
   c.cid,c.name,sum(d.sales)
from sales_companies c
join sales_divdetails d on c.cid = d.cid
where c.subsector=:subsector $region and d.syear=$y $wsize
group by c.cid, c.name
order by d.sales desc
limit 20";
                $years[$y] = array();
                $qr = $db->query($sql, $prm);
                $i=1;
                while ( $r = $db->fetchSingle($qr) )
                {   $years[$y][$r->cid] = $i;
                    $i++;
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
            // log stability 
            //write_log(print_r($years, true));
            //write_log(print_r($changes, true));
            if ($n!=0) $stability = $sum/$n;
            else $stability='NULL';
            //write_log("stability = $stability");
        }

        
        
        $sql = "select sum(t.sales) from (select c.sales from sales_companies c where c.subsector=:subsector $region $wsize order by 1 desc limit 3) t";
        $qr = $db->query($sql, $prm);
        $top3sum = $db->fetchSingleValue($qr);
        if (empty($top3sum)) $top3sum = '0';

        $sql = "select sum(t.sales) from (select c.sales from sales_companies c where c.subsector=:subsector $region  $wsize order by 1 desc limit 5) t";
        $qr = $db->query($sql, $prm);
        $top5sum = $db->fetchSingleValue($qr);
        if (empty($top5sum)) $top5sum = '0';
        
        $sql = "select c.subsector, sum(c.sales) as tsales, $top3sum as top3sum, $top5sum as top5sum,
avg(c.sales_growth) as asales_growth, 
avg(c.roic) as aroic,
avg(c.pe) as ape,
avg(c.evebitda) as aevebitda,
avg(c.payout) as apayout,
$stability as stability,
sum(c.reviewed)/count(*) as previewed
        from sales_companies c
where c.subsector=:subsector $region $wsize
group by 1
order by 1";
       $qr = $db->query($sql, $prm);
       $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
       echo json_encode($this->res);
    } 
    
    function getExposuresByPortfolio($ha)
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
        $r = new stdClass();
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
        
        $sql = "select headers from sales_exposure";
        $qr = $db->query($sql);
        $ha = explode(';', $db->fetchSingleValue($qr));
        
        $db->query("set @year=:year;", array('year'=>$yr->maxyear));
        
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
       $db->query($sql);
       
        
        
        $db->query("set @pf=:pf;", array('pf'=>$params->pf1));        
        $this->res->data1 = $this->getExposuresByPortfolio($ha);
        
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
    
 
 }

?>
