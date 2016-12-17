<?php
   class authMenu extends wMod
   {  function inAcl($r)
      { foreach($r as $v) 
        { if (!$this->cfg->inGroup($v)) return false;
        }
        return true;
      }

       function display()
     {   $user = $this->cfg->user->user;
         if (isset($user->id)) 
         {
?>
<ul class="nav navbar-nav navbar-right">
<li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$user->name?>&nbsp;<span class="caret"></span></a>
                <ul class="dropdown-menu">
<?php      
       foreach($this->data as $r)
       { $ac = '';
         if ($this->cfg->nav==$r->a) $ac=' class="active"';
         $url = mkURL($r->a);
         if (property_exists($r,'acl'))
         { if ($this->inAcl($r->acl)) echo "<li$ac><a href=\"$url\">".T($r->t)."</a></li>";
         } else   echo "<li$ac><a href=\"$url\">".T($r->t)."</a></li>";
       }
?>
                  <li role="separator" class="divider"></li>
                  <li><a href="javascript:logoutForm.submit()"><?=T('Logout')?></a></li>
                </ul>
</li>
</ul>
<form id="logoutForm" name="logoutForm" method="POST" action="<?=mkURL('/')?>" style="display:none"><input type="hidden" name="logout" value="1"></form>
<?php
         }
         else 
         {
?>
<ul class="nav navbar-nav navbar-right">
  <li class="active"><a href="<?=mkURL('/login')?>"><?=T('Login')?></a>
</ul>
<?php
         }
     }
   }
?>
