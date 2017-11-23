<?php
 /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
 // include('../lib/params.php');
 // include('../lib/phpmailer.php');

 class wAjaxModel extends wAjax
 {  var $where_parts;
    var $order_parts;
    var $cur_page;
     
    function processModel($dir)
    {   $this->cur_page = null;
        $db = $this->cfg->db;
        $this->where_parts = array();
        if (isset($this->seg[3]))
        {   if (!isset($this->seg[4])) return $this->error(T('METHOD_IS_ABSENT'),__LINE__);
            $mname = $this->seg[3];
            $mfile = $dir."/models/model.$mname.js";
            if (!file_exists($mfile)) return $this->error(T('FILE_NOT_FOUND').' '.$mfile,__LINE__);
            else 
            { $mod=json_decode( file_get_contents($mfile) );
              if (json_last_error()!=JSON_ERROR_NONE)
              return $this->error(T('JSON_ERROR').' '.json_last_error_msg(),__LINE__);
              $this->model = $mod;
            }
            $method = $this->seg[4];
            switch ($method)
            {
                case 'load': $this->modelLoad($mod); break;
                case 'row': $this->modelRow($mod); break;
                case 'delete': $this->modelDelete($mod); break;
                case 'insert': $this->modelInsert($mod); break;
                case 'update': $this->modelUpdate($mod); break;
                default: return $this->error(T('UNKNOWN_METHOD').' '.$method,__LINE__);
            }
        } else return $this->error(T('MODEL_NAME_IS_ABSENT'),__LINE__);
    }
    
    function SQLVars($sql)
    {  $sql = str_replace('$table',$this->model->table, $sql);
       $sql = str_replace('$where',$this->mkWhereSQL(), $sql);
       $sql = str_replace('$order',$this->mkOrderSQL(), $sql);
       $sql = str_replace('$limit',$this->mkLimitSQL(), $sql);       
       if (isset($this->cfg->user->user))
       {  $sql = str_replace('$UID',$this->cfg->user->user->id, $sql);
       }
       $sql = str_replace('$limit',$this->mkLimitSQL(), $sql);
       return $sql;
    }
    
    function mkLimitSQL()
    {   if ( isset($this->model->rows_number_limit) )
        {  $rows_lim = 1*$this->model->rows_number_limit;
           if ($this->cur_page==null) return " LIMIT $rows_lim";
           $offset = ($this->cur_page-1)*$rows_lim;
           return " LIMIT $offset,$rows_lim";
        }
    }
    
    function mkWhereSQL()
    {   $filt = implode(' and ', $this->where_parts);
        if (trim($filt)!='') return " where $filt ";
        return '';
    }
    
    function mkOrderSQL()
    {  if (!empty($this->order_parts))
       {  return ' order by '.implode(',', $this->order_parts).' ';
       }
       if ( isset($this->model->default_order) )
       return ' order by '.$this->model->default_order.' ';
    }
    
    function modelTotal($params)
    {  $db = $this->cfg->db;
       if ( isset($this->model->select_total) )
       {  $qr = $db->query( $this->SQLVars($this->model->select_total), $params);
          $this->res->total =  $db->fetchSingleValue($qr);
          return $this->res->total;
       }
       return null;
    }
      
    function modelLoad($model)
    {  $db = $this->cfg->db;

       $params = (object)$_POST;
       if (isset($params->search) && isset($this->model->search) )
       {    $this->where_parts[] = '('.$this->model->search.')';
       }
       
       if (isset($this->model->filter_parts))
       {   $fp = $this->model->filter_parts;
           foreach ((array)$params as $k=>$v)
           { if (isset($fp->$k)) $this->where_parts[] = $fp->$k;
           }
       }
       
       if (isset($this->model->permanent_filter))
       {   $this->where_parts[] = $this->model->permanent_filter;
       }
       
       if (isset($params->order))
       {  $this->order_parts = array();
          foreach ($params->order as $col)
          { $d = '';
            $r = (object)$col;
            if (isset($r->desc) && $r->desc==true) $d = ' desc';
            if (isset($r->col)) 
            {   $fcol = filter_var($r->col,FILTER_SANITIZE_STRING);
                $this->order_parts[] = "$fcol$d";
            }
          }
          // $this->res->order = $params->order;
          unset($params->order);
       }

       $this->modelTotal($params);
       
       if (isset($this->seg[5])) $this->cur_page=$this->seg[5];
       
       if ( isset($this->model->rows_number_limit) )
       $this->res->rows_number_limit = $this->model->rows_number_limit;
       
       
       if ( isset($this->model->list_columns) )
       {  $this->res->titles = array();
          $lk = explode(',', $this->model->list_columns);          
          foreach($lk as $v) 
          $this->res->titles[] = T($v);
          $this->res->columns = $lk;
       }
       
       if ( isset($this->model->primary_keys) )
       $this->res->primary_keys = explode(',', $this->model->primary_keys);
       
       if ( isset($this->model->select) )
       {  $qr = $db->query( $this->SQLVars($this->model->select), $params );
          $this->res->rows =  $qr->fetchAll(PDO::FETCH_OBJ);
       }
       
       if (isset($model->afterLoad))
       {   $method = $model->afterLoad;            
            if (!method_exists($this, $method))
             return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__);                          
            $this->$method();
       }
              
       echo json_encode($this->res);
    }

    function modelRow($model)
    {   $db = $this->cfg->db;
               
        if ( isset($this->model->select_row) )
        {   
            if (isset($this->seg[5])) 
            { 
                $id=$this->seg[5];
                $qr = $db->query( $this->SQLVars($this->model->select_row), 
                  array('id'=>$id) );
            } else 
            {   $params = (object)$_POST;
                $qr = $db->query( $this->SQLVars($this->model->select_row), $params);
            }
            $this->res->row = $db->fetchSingle($qr);
        }
        echo json_encode($this->res);
    }

    function checkAccess($model, $option)
    {  if (!isset($model->$option)) return true;       
       foreach($model->$option as $group)
       { if ($this->cfg->inGroup($group)) return true;
       }
       $this->error(T('NOT_IN_GROUP').': '.implode(', ',$model->$option),__LINE__);
       return false;
    }

    function modelDelete($model)
    {   if (!$this->checkAccess($model,'allow_delete')) return;
        if (isset($model->delete))
        {   $params = (object)$_POST;
            $db = $this->cfg->db;
            if (isset($model->beforeDelete))
            {  $method = $model->beforeDelete;
               if (!method_exists($this, $method))
                return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__); 
               $this->$method($params);
            }
            $sql = $this->SQLVars($model->delete);
            $db->query($sql, $params);
        } else return $this->error(T('DELETE_MODEL_PARAM_NOT_FOUND'),__LINE__);
        echo json_encode($this->res);
    }

    function modelInsert($model)
    {   if (!$this->checkAccess($model,'allow_insert')) return;
        $row = (object)$_POST;
        $db = $this->cfg->db;
        
        if (isset($model->beforeInsert))
        {   $method = $model->beforeInsert;            
            if (!method_exists($this, $method))
             return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__);                          
            $this->$method($row);
        }
        
        $db->insertObject($model->table,$row);
        $this->res->id = $db->db->lastInsertId();
        $this->res->info = T('Saved');
        
        if (isset($model->afterInsert))
        {   $method = $model->afterInsert;            
            if (!method_exists($this, $method))
             return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__);                          
            $this->$method($row);
        }
        echo json_encode($this->res);
    }
    
    function modelUpdate($model)
    {   if (!$this->checkAccess($model,'allow_update')) return;
        $row = (object)$_POST;
        $db = $this->cfg->db;
        
        if (!isset($model->primary_key))
         return $this->error(T('PRIMARY_KEY_NOT_FOUND').' '.$method,__LINE__);
        
        $key = explode(',', $model->primary_key);
        $keys = array();
        foreach($key as $k)
        { if (isset($row->$k)) 
          { $keys[$k] = $row->$k;
            unset($row->$k);
          }
        }
        
        if (isset($model->beforeUpdate))
        {   $method = $model->beforeUpdate;            
            if (!method_exists($this, $method))
             return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__);                          
            $this->$method($row,$keys);
        }
        
        $this->res->k = $keys;
        $this->res->r = $row;
        
        $db->updateObject($model->table, $row, $keys);
        
        $this->res->info = T('Saved');
        
        if (isset($model->afterUpdate))
        {   $method = $model->afterUpdate;            
            if (!method_exists($this, $method))
             return $this->error(T('METHOD_NOT_FOUND').' '.$method,__LINE__);                          
            $this->$method($row, $keys);
        }
        echo json_encode($this->res);
    }
    
    /* 
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
    */

 }

?>
