<?php
   class wMenu extends wMod
   { function display()
     { $cf = $this->cfg;
       if ($this->cfg->db!=null)
       {  $qr = $this->cfg->db->query('select id, name, vtitle from md_views where conf_id=:id order by vtitle', array('id'=>$cf->md_conf) );
           while ( $r = $qr->fetch(PDO::FETCH_OBJ) )
           { $ac = '';
             if ($this->cfg->nav=="view/$r->name") $ac=' class="active"';
             echo "<li$ac><a href=\"/index.php/view/$r->name\">$r->vtitle</a></li>";
           }
        }
      }
   }
?>
