<?php  
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 include(SYS_PATH.'lib/ajaxmodel.php');
  
    class ajxadmin extends wAjaxModel
    {  
      
     function ajxModel()
     {  $this->includePageLocales(__DIR__);
        if (!$this->authGroup('admin')) return $this->error(T("ERR_NOT_ADMIN"), true);
        $this->processModel(__DIR__);
      }
      
      function ajxAll()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $qr = $db->query('select id,name,lastname,firstname,email,phone from mc_users order by lastname, firstname');
          $this->res->data= $qr->fetchAll(PDO::FETCH_OBJ);
          echo json_encode($this->res);
       }
       
       function ajxLoadTmpl()
       {   if (!$this->authGroup('admin')) return;
           $db = $this->cfg->db;
           $tmpl = $this->seg[3];
           $qr = $db->query('select * from templates where name=:name',
           array('name'=>$tmpl));
           $this->res->row = $db->fetchSingle($qr);            
           echo json_encode($this->res);
       }
       
       function ajxSaveTmpl()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $r = (object)$_POST;
          $key = $r->seltmpl;
          unset($r->seltmpl);
          if ($db->updateObject('templates', $r, array('name'=>$key)))
          $this->res->info = T('TEMPLATE_SAVED');
          echo json_encode($this->res);
       }

       function ajxLoadTable()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $r = (object)$_POST;
          $table = $this->seg[3];
          $parent_id = null;
          if (isset($this->seg[4])) $parent_id=$this->seg[4];
          $lim=25;
          $tables = json_decode(file_get_contents(__DIR__.'/models.js'));
          // $this->res->lasterror = json_last_error_msg();
          // $this->res->tables = $tables;

          if (isset($tables->$table))
          {   $t = $tables->$table;
              $sql = $t->query;
              $limit = " limit $lim";
              
              $params = array();
              
              if (isset($t->where) && isset($r->search))
              {   $sql = str_replace('$where', " where $t->where ",$sql);
                  $params = (array)$r;
                  $limit='';
              }
              
              if ($parent_id!=null) $params['id'] = $parent_id;
              
              $qr = $db->query($sql.$limit, $params);
              
              $this->res->rows= $qr->fetchAll(PDO::FETCH_OBJ);
              
              if (isset($t->table))
              {  $qr = $db->query("select count(*) from $t->table");
                 $this->res->total = $db->fetchSingleValue($qr);
              } 
              if ($table=='evnamegl_guests' && $parent_id!=null)
              { $qr = $db->query('select * from evnamegl where id=:id',
                array('id'=>$parent_id));                
                $this->res->head = $db->fetchSingle($qr);
              }
              
          } else return $this->error(T('ACCESS_DENIED'), 1041);
          
          echo json_encode($this->res);
       }
              
       function ajxSaveEmailSettings()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $r = (object)$_POST;
          $d = new stdClass();
          $d->json = json_encode($r);
          $this->res->rd = $d;
          if ($db->updateObject('settings', $d, array('name'=>'email')))
          $this->res->info = T('EMAIL_SETTINGS_SAVED');
          echo json_encode($this->res);
       }
       
       function ajxLoadEmailSettings()
       {   if (!$this->authGroup('admin')) return;
           $db = $this->cfg->db;
           $qr = $db->query("select json from settings where name='email'");
           $this->res->row = json_decode($db->fetchSingleValue($qr));
           echo json_encode($this->res);
       }
       
       function ajxSavePassword()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $r = (object)$_POST;
          if ($r->enewpass1!=$r->enewpass2)
          { return $this->error(T('PW_DONT_MATCH'), 1058);
          }
          
          $auth = $this->cfg->user;
          $user = $auth->user;
          $info = null;
          if (!$auth->checkUserPassword($user->name, $r->epassold, $info))
          { return $this->error(T('PW_WRONG_OLD'), 1066);
          }
          
          $pass = $auth->hashPassword($r->enewpass1);
          try
          {
            $db->query('update mc_users set pass=:pass where id=:id',
            array('pass'=>$pass, 'id'=>$user->id));
            $this->res->info = T('PASSWORD_CHANGED');  
            echo json_encode($this->res);
          } catch (Exception $e)
          {  return $this->error($e->getMessage(), $e->getCode());
          }
       }
       
       function ajxSaveUserGroups()
       { if (!$this->authGroup('admin')) return;
         $db = $this->cfg->db;
         $r = (object)$_POST;
         $uid = 1*$r->user_id;
         if ($uid==$this->cfg->getUID()) return $this->error(T('CAN_NOT_MODIFY_YOURSELF'), 5120);
         try
         {
             $qr = $db->query("select group_id from mc_usergroups where user_id=:id",
             array('id'=>$uid));
             while ($tr = $db->fetchSingle($qr))
             { $gid = $tr->group_id;
               if (isset($r->groups[$gid]))
               { if ($r->groups[$gid]==0) $db->query("delete from mc_usergroups where user_id=:uid and group_id=:gid",
                 array('uid'=>$uid, 'gid'=>$gid)); 
                 else unset($r->groups[$gid]); // if already added then no need to add
               }
             }
             foreach($r->groups as $k=>$v)
             {  if ($v==1) $db->query("insert into mc_usergroups (user_id, group_id) values (:uid,:gid)",
                 array('uid'=>$uid, 'gid'=>$k)); 
             }
             $this->res->info = T('Saved');
          } catch (Exception $e)
          {  return $this->error($e->getMessage(), $e->getCode());
          }
          echo json_encode($this->res);
       }
       
       function beforeInsertUser(&$row)
       {  $auth = $this->cfg->user;
          $row->pass = $auth->hashPassword($row->pass);
          if (isset($row->pass2)) unset($row->pass2);
       }

       function beforeUpdateUser(&$row)
       {  $auth = $this->cfg->user;
          if (isset($row->pass))
          { if ($row->pass=='') unset($row->pass);
            else  $row->pass = $auth->hashPassword($row->pass);
          }
          if (isset($row->pass2)) unset($row->pass2);
       }
        
       function beforeDeleteUser($row)
       { $uid = 1*$row->id;
         if ($uid==$this->cfg->getUID())
            throw new Exception(T('CAN_NOT_DELETE_YOURSELF'));
       }

    }
?>
