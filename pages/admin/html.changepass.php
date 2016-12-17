<h2>Change password</h2>
<?php  
   $user = $this->user->user;
   if ($user->auth_module!='')
   {
        echo '<div class="alert alert-warning">';
        printf(T('YOU_AUTH_VIA_OAUTH'), strtoupper($user->auth_module));
        echo '</div>';
   }
   else
   {
?>
<form id="change-password">
  <div class="form-group">
    <label for="epassold">Old Password</label>
    <input type="password" class="form-control" id="epassold" placeholder="Old Password">
  </div>
  <div class="form-group">
    <label for="enewpass1">New Password</label>
    <input type="password" class="form-control" id="enewpass1" placeholder="New Password">
  </div>
  <div class="form-group">
    <label for="enewpass2">New Password</label>
    <input type="password" class="form-control" id="enewpass2" placeholder="New Password">
  </div>
  <button type="button" class="btn btn-default  btn-default btn-info btn-lg" id="btchangepw">Change</button>
</form>
<?php
  }
?>
