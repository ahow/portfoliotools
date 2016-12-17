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
    
    function ajxForm1()
    {   $db = $this->cfg->db;
        $params = (object)$_POST;
        $qr = $db->query('select s.id, s.sales,d.division as number, ig.division as sic_division,
         ig.major_group, d.syear, d.me, d.sic, d.sales,sic.name as sicname, ig.industry_group, d.id as divdetail_id
         from sales_div_sales s
        join sales_divdetails d on s.id=d.div_sale_id
        join sales_sic sic on d.sic=sic.id
        join sales_industry_groups ig on sic.industry_group_id=ig.id
         where cid=:cid and d.syear<=:year order by d.division, d.syear',$params);
        $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($this->res);
    }
 }

?>
