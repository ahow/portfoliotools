<?php
    /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
   
   class db extends wMod
   { var $db;
     function __construct($cfg, $path, $data='')
     { $this->cfg = $cfg;
       $this->path = $path;
       $db = null;
              
       $dbconn = "$cfg->dbtype:host=$cfg->dbhost;dbname=$cfg->dbname";
       $db = new PDO($dbconn, $cfg->dbuser, $cfg->dbpass );
       $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       $db->exec("set names $cfg->dbcharset;");
              
       if ($db!=null)
       {   unset($cfg->dbuser);
           unset($cfg->dbpass);
           unset($cfg->dbconn);
       }
       $this->db = $db;
       return $db;
    }

    function insertObject($table, $r)
    {  $ins = array();
       $values = (array)$r;
       $flds = array();
       foreach ($values as $field => $v) 
       { $ins[] = ':' . $field;
         $flds[] = '`'.$field.'`';
       }

       $ins = implode(',', $ins);
       $fields = implode(',', $flds);
       $sql = "INSERT INTO $table ($fields) VALUES ($ins);";
       // echo $sql."\n";
       $sth = $this->db->prepare($sql);
       foreach ($values as $f => $v)
       { $sth->bindValue(':' . $f, $v);
       }
       $sth->execute();
       return true;
       //return $db->lastInsertId()
    }

    function updateObject($table, $r, $keys)
    {  $values = (array)$r;
       $sts = array();
       $ks = array();
       
       if (empty($keys)) 
       {   throw new Exception(T('EMPTY_KEYS')); 
       }
       
       foreach ($keys as $k=>$v) $ks[] = "$k=:$k";
       foreach ($values as $field => $v) $sts[] = '`'.$field.'`=:'.$field;
       $sets = implode(',', $sts);
       $sql = "update $table set $sets where (".implode(' and ',$ks).") ";
       $sth = $this->db->prepare($sql);
       foreach ($values as $f => $v) 
       { if ($v=='null') $v=null;
         $sth->bindValue(':' . $f, $v);
       }
       foreach ($keys as $f => $v) $sth->bindValue(':' . $f, $v);
       $sth->execute();
       return true;
    }
        
    function query($sql, $params=null)
    {  $GLOBALS['SQLQuery'] = $sql;
       $GLOBALS['SQLParams'] = $params; 
       $sth = $this->db->prepare($sql);
       if ($params!=null) foreach ($params as $f => $v) $sth->bindValue(':' . $f, $v);
       $r = $sth->execute();
       unset($GLOBALS['SQLQuery']);
       unset($GLOBALS['SQLParams']);
       return $sth;
    }
    
    function selectObjects($table, $columns, $keys=null, $order='')
    {  if ($columns=='*') $flds=' * '; else $flds = implode(',', $columns);
       $ord='';
       if ($order!='') $ord = ' ORDER BY '.implode(',',$order);
       if ($keys!=null)
       {   $ks = array();
           foreach ($keys as $k=>$v) $ks[] = "$k=:$k";
           $sql = "SELECT $flds from  $table where (".implode(' and ',$ks).")  $ord";
           $sth = $this->db->prepare($sql);
           foreach ($keys as $f => $v) $sth->bindValue(':' . $f, $v);
       } else $sth = $this->db->prepare("SELECT $flds from  $table $ord");
       try
       {  $r = $sth->execute();
          return $sth;
       }  catch (PDOException  $e)
       {  $this->cfg->setError("Line: ".$e->getLine()."\n".$e->getMessage()."\n", 902);          
          return false;
       }
    }
    
    function fetchSingle($qr){ if ($qr!==false) return $qr->fetch(PDO::FETCH_OBJ); return false; }
    
    function fetchSingleValue($qr)
    { if ($qr!==false)
      { $a = $qr->fetch(PDO::FETCH_NUM);
        return $a[0]; 
      }return false; 
    }
    
    function deleteObject($table, $keys)
    {  $ks = array();
       foreach ($keys as $k=>$v) $ks[] = "$k=:$k";
       $sql = "DELETE FROM  $table where (".implode(' and ',$ks).") ";
       $sth = $this->db->prepare($sql);
       foreach ($keys as $k=>$v) $sth->bindValue(':'.$k, $v);
       return $sth->execute();
    }
   }

?>
