<?php
  class oauth extends wPage
  {  function oauth($cfg, $path, $seg=null)
     {  $cfg->title = 'OAUTH';
        //$this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->seg = $seg;
        if (isset($seg[1]))
        {   $srv = $seg[1];
            $this->service = $srv;
            include("../pages/oauth/$srv.php"); 
        }
     }
     function display()
     { 
     }
     
     function afterInit()
     { if ($this->oauth!=null) 
       {   //return;
           $db = $this->cfg->db;
           $uname = "$this->service\\".$this->oauth->name;
           $qr = $db->query("select id from mc_users where auth_id=:id",
            array('id'=>$this->oauth->id));
           $r = $db->fetchSingle($qr);
           
           if (empty($r))
           { $r = new stdClass();
             $r->name = $uname;
             $r->firstname = $this->oauth->given_name;
             $r->lastname = $this->oauth->family_name;
             $r->email = $this->oauth->email;
             $r->image = $this->oauth->picture;
             $r->auth_id = $this->oauth->id;
             $r->auth_module = $this->service;
             $r->pass = '::::';
             $db->insertObject('mc_users',$r);
             $uid = $db->db->lastInsertId();
           } else $uid = $r->id;
           $this->cfg->user->logout();
           $this->cfg->user->startSession($uid);
           $this->cfg->user->checkAuth();
           header('Location: '.mkURL('/login'));
       }
        
     }
  }
?>
