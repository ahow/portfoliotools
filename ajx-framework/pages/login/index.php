<?php
   /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  if (!empty($this->cfg->user)) $user = $this->cfg->user->user;
  if (!empty($user))
  {  $name = $user->firstname;
     if ($user->lastname!='') $name.=' '.$user->lastname;
     echo '<h1>'.T("WELCOME").' '.$name.'!</h1>';
     echo '<form method="POST"><input type="hidden" name="logout" value="1" /><button type="submit" class="btn  btn-info btn-lg ">'.T('Logout').'</button></form>';  
  } else
  {
?>
<h1><?=T('Login') ?></h1>
<form method="POST">
  <div class="form-group">
    <label for="uname"><?=T('Login_name') ?></label>
    <input type="text" class="form-control" id="uname" name="uname" placeholder="<?=T('Login_name') ?>" >
  </div>
  <div class="form-group">
    <label for="upass"><?=T('Password') ?></label>
    <input type="password" class="form-control" id="upass" name="upass" placeholder="<?=T('Password') ?>">
  </div>  
  <button type="submit" class="btn btn-default btn-info btn-lg "><?=T('Login') ?></button>
  <br><br>
  <?php
    $cfile = substr(__DIR__,0,-5).'oauth/config.js';
    $refs = '';
    if (file_exists($cfile))
    {  $cfg = json_decode(file_get_contents($cfile));       
       foreach($cfg as $r)
       { if ($r->enabled) $refs.='<a href="'.mkURL('/oauth/'.$r->name).'">'.$r->title.'</a> ';
       }
    }
    if ($refs!='')  echo T('SIGN_IN_WITH').': '.$refs;    
  ?>  
</form>
<?php
  }
?>
