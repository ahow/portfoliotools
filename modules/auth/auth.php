<?php
    /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */

   class auth extends wMod
   { var $user = null;
     var $defaultURL = '';
   
     function getIPAddr()
     {  $ip_rem = '0.0.0.0';
        $ip_loc = '0.0.0.0';
        // Workaround of Chrome's bug in Android
        // if (isset($_SERVER['REMOTE_ADDR'])) $ip_rem=$_SERVER['REMOTE_ADDR'];
        //if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip_loc = $_SERVER['HTTP_X_FORWARDED_FOR'];
        // write_log("IPS $ip_rem, $ip_loc");
        return ip2hex($ip_rem).ip2hex($ip_loc);
     }
     function bkeygen($l)
     {  $r='';
        for ($i=0; $i<$l; $i++) $r.= dechex(rand(0,15));
        return $r;
     }
     
     function inGroup($grp)
     { if ($this->user!=null && isset($this->user->groups[$grp])) return true;
       return false;
     }
     
    function getInf($o)
    {  $user = new StdClass();
       $user->id = filter_var($o->id,FILTER_SANITIZE_NUMBER_INT);
       $user->name = filter_var($o->name, FILTER_SANITIZE_STRING);
       $user->firstname = filter_var($o->firstname, FILTER_SANITIZE_STRING);
       $user->lastname = filter_var($o->lastname, FILTER_SANITIZE_STRING);       
       $user->auth_module = filter_var($o->auth_module, FILTER_SANITIZE_STRING); 
          
       $db = $this->cfg->db;
       $sql = 'select g.grname from mc_usergroups ug '
       .'join mc_users u on ug.user_id=u.id '
       .'join mc_groups g on ug.group_id=g.id '
       .'where u.name = :name';
       $qr = $db->query($sql, array('name'=>$user->name) );
       $groups = array();
       while ( $r = $qr->fetch(PDO::FETCH_OBJ) )       
       { $groups[$r->grname] = 1;
       }
       
       $user->groups = $groups;
       $this->user = $user;
       return $user;
     }
     
     function clearSession()
     { // Удалим старую сессию, чтобы зайти с новым именем
       $db =  $this->cfg->db;
       if (isset($_COOKIE['_usid']))
       { $usid = $_COOKIE['_usid'];
         $db->query("delete from mc_sessions where session=:sid", array('sid'=>$usid) );
         unset($_COOKIE['_usid']);
         setcookie('_usid', null, -1, '/');
       }
     }
     
     function hashPassword($password)
     {  return password_hash($password, PASSWORD_BCRYPT);
     }
     
     function verifyPassword($password, $hash)
     {  return password_verify($password, $hash);         
     }
     
     function checkUserPassword($user, $password, &$o)
     {  $db =  $this->cfg->db;        
        $upass = $this->hashPassword($password);
        $sql = "select id, name, lastname, firstname, pass, auth_module from mc_users where name=:name";
        $qr = $db->query($sql, array('name'=>$user) );
        $o = $db->fetchSingle($qr);
        if (!empty($o))
        {  if ( $this->verifyPassword($password, $o->pass) )
           {  unset($o->pass);
              return true;
           }
        }
        $o = null;
        return false;
     }

    function startSession($uid)
    { $db =  $this->cfg->db;
      if ($db==null) return false;
      $session = $this->getIPAddr().$this->bkeygen(32);
      $ttl = time()+36000*24*30; // время
      $sql = "insert into mc_sessions (user_id, session, ttl) values (:uid, :sess, :ttl)";
      $db->query($sql, array('uid'=>$uid, 'sess'=>$session, 'ttl'=>$ttl) );
      setcookie('_usid', $session , $ttl, '/');
      return true;
    }

     // Authorization module
     function checkAuth()
     { $db =  $this->cfg->db;
       if ($db==null) return false;
       
       if(session_id() == '') 
       {  session_start();
       }
       
       if (isset($_POST['uname']) && isset($_POST['upass']))
       {
           $this->clearSession();           
           $uname = filter_var($_POST['uname'], FILTER_SANITIZE_STRING);
           
           if ($this->checkUserPassword($uname, $_POST['upass'],$o))
           {  $u = $this->getInf($o);
              $s = $this->startSession($u->id);
              if (isset($this->cfg->authorizedURL)) header('Location: '.$this->cfg->authorizedURL);
              return $s;
           }
           
         } else
         // session key checking
         if (isset($_COOKIE['_usid']))
         {  $usid = $_COOKIE['_usid'];
            // write_log('COKIE _usid '.$usid);
            $sess = $this->getIPAddr().substr($usid, 16); // сверим IP
            // write_log(' $sess =  '.$sess);
                
            $sql = "select * from mc_sessions where session=:sess";
            $qr = $db->query($sql, array('sess'=>$sess) );
            $o = $db->fetchSingle($qr);
            if (!empty($o)) 
            {   $sql = "select id, name, lastname,firstname,auth_module from mc_users where id=".$o->user_id;
                $qr = $db->query($sql, array('uid'=>$o->user_id) );
                $o = $db->fetchSingle($qr);
                // write_log('$o =  '.print_r($o, true));
                if (!empty($o)) 
                {   $this->getInf($o);
                    return true;
                } else sleep(3);
            }
            return false;
         }
    }
    
    function logout()
    { if (isset($_POST['logout']) && ($_POST['logout']==1) ) $this->clearSession();
    }
    
     function auth($cfg, $path, $data='')
     { $this->cfg = $cfg;
       $this->path = $path;
       
       $this->logout();
       $this->checkAuth();       
     }

   }


  // IP address to 16 char hex value
  function ip2hex($ip)
  { $a = explode('.',$ip);
    $r = '';
    if (count($a)<4) return '00000000'; // IPv6 hack
    for ($i=0; $i<4; $i++)
    { $n = (1*$a[$i]) & 0xff;
      $r.=  dechex($n >> 4).dechex($n & 0xf);
     }
     return $r;
   }
?>
