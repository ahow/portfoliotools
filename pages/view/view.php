<?php
  class view extends wPage
  {  var $seg;
     function view($cfg, $path, $seg=null)
     {  $cfg->title = 'Документы';
        $this->path = $path.'/index.php';
        $this->cfg = $cfg;
        $this->seg = $seg;
        $cfg->addJS('/js',"view.js");
     }
     function drawView($qr, $h)
     { echo '<table class="table table-striped"><tr>';
       foreach ($h as $r) echo "<th>$r->ftitle</th>";
       echo "</tr>";
       while ( $r = $qr->fetch(PDO::FETCH_OBJ) )
       {  echo '<tr>';
          foreach ($h as $f) 
          { $k = $f->fname;
            if (isset($r->$k)) echo '<td>'.$r->$k.'</td>';
            else echo '<td>-</td>';
          }
          echo '</tr>';
       }
       echo "</table>";
     }
     
     function display()
     {  if (isset($this->seg[1]))
        { $v = $this->seg[1];
          /*$db = $this->cfg->db;
          $qr = $db->query('select id,vtitle,tname from md_views where name=:name and conf_id=:id',
           array('name'=>$v, 'id'=>$this->cfg->md_conf) );
          $vr = $db->fetchSingle($qr);
          $qr->closeCursor();
          $qr = $db->query('select * from md_fields where view_id=:id and visable=1 and ingrid=1 order by id',
          array('id'=>$vr->id) );
          $h = array();
          while ( $r = $qr->fetch(PDO::FETCH_OBJ) ) $h[] = $r;
          $qr->closeCursor();
          $qr = $db->query('select * from '.$vr->tname);
          
          echo '<h1>'.$this->cfg->title.' / '.$vr->vtitle.'</h1>';
          $this->drawView($qr, $h); */
          echo '<div class="w-view" data-view="'.$v.'"></div><div id="linked-modals"></div>';
          //echo '<div class="w-view" data-view="kafedra"></div>';
        }
     }
  }
?>
