<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include('../lib/params.php');
 // include('../lib/phpmailer.php');
 include('../lib/ajaxmodel.php');
 
 class ajxmedinfo extends wAjaxModel
 {  
     
    function ajxModel()
    {   $this->includePageLocales(__DIR__);
        $this->processModel(__DIR__);
    }
    
    // Set user id ti created_by field
    function bi_Patient(&$row)
    {   $row->created_by = $this->cfg->user->user->id;
    }
        
    function ajxSave()
    {   $db = $this->cfg->db;        
        $id = $this->cfg->user->user->id;
        $r = new stdClass();
        $d = (object)$_POST;
        $r->email = filter_var($d->email, FILTER_SANITIZE_EMAIL); 
        $r->phone = filter_var($d->phone, FILTER_SANITIZE_NUMBER_INT); 
        $r->firstname = filter_var($d->firstname, FILTER_SANITIZE_STRING); 
        $r->lastname = filter_var($d->lastname, FILTER_SANITIZE_STRING);
        $db->updateObject('mc_users',$r,array('id'=>$id));        
        $this->res->info = T('Saved');        
        echo json_encode($this->res);
    }
    
 }

?>
