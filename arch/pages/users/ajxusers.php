<?  class ajxusers extends wAjax
    {  function ajxAll()
       {  if (!$this->authGroup('admin')) return;
          $db = $this->cfg->db;
          $qr = $db->query('select id,name,lastname,firstname,email,phone from mc_users order by lastname, firstname');
          $this->res->data= $qr->fetchAll(PDO::FETCH_OBJ);
          echo json_encode($this->res);
       }
    }
?>
