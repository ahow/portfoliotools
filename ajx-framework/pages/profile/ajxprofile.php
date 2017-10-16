<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include(SYS_PATH.'lib/params.php');
 // include(SYS_PATH.'lib/phpmailer.php');
  
 class ajxprofile extends wAjax
 {  function ajxLoad()
    {   $db = $this->cfg->db;
        //$email = filter_var($d->email, FILTER_SANITIZE_EMAIL);
        // 
        // $this->res->row 
        if (!isset($this->cfg->user->user)) return $this->error(T('ACCESS_DENIED'), 1041);
        $id = $this->cfg->user->user->id;
        $qr = $db->query("select id,firstname,lastname,email,phone from mc_users where id=:id",
        array('id'=>$id));
        $this->res->row = $db->fetchSingle($qr);
        echo json_encode($this->res);
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
