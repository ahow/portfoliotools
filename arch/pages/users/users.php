<?php
  class users extends wPage
  {  function users($cfg, $path, $seg=null)
     {  $cfg->title = 'Users';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
     }
     function drawTable($qr, $h)
     { echo '<table class="table table-striped"><tr>';
       foreach ($h as $v) echo "<th>$v</th>";
       echo "</tr>";
       while ( $r = $qr->fetch(PDO::FETCH_OBJ) )
       {  echo '<tr>';
          foreach ($h as $k=>$v) echo '<td>'.$r->$k.'</td>';
          echo '</tr>';
       }
       echo "</table>";
     }
     
     function display()
     { if ( $this->cfg->inGroup('admin') )
       {
           echo '<h1>'.T($this->cfg->title).'</h1>';
           $qr = $this->cfg->db->query('select id,name,lastname,firstname,email,phone from mc_users order by lastname, firstname');
           // $i = 10/0;
          
           $this->drawTable($qr, array('id'=>T('ID'), 'name'=>T('User'),'firstname'=>T('Name')) );

        } else echo T("AUTH_REQURED");
     }
  }
?>
