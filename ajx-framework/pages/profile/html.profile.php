<?php
  //$db = $this->db;
  //$qr = $db->query("select autorespsubj, autorespbody from templates where name='signup'");
  //$r = $db->fetchSingle($qr);
?>
 
<div id="form" class="form-horizontal hidden">
    <div class="form-group">
        <label for="firstname" class="control-label col-xs-2">First Name</label>
        <div class="col-xs-8">
            <input type="email" class="form-control" id="firstname" placeholder="First Name" data-validate="req,minlen=2">
        </div>
    </div>
    <div class="form-group">
        <label for="lastname" class="control-label col-xs-2">Last Name</label>
        <div class="col-xs-8">
            <input type="email" class="form-control" id="lastname" placeholder="Last Name" data-validate="req,minlen=2">
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="control-label col-xs-2">Email</label>
        <div class="col-xs-8">
            <input type="email" class="form-control" id="email" placeholder="Email" data-validate="req,email">
        </div>
    </div>
    <div class="form-group">
        <label for="phone" class="control-label col-xs-2">Phone</label>
        <div class="col-xs-8">
            <input type="email" class="form-control" id="phone" placeholder="Phone" data-validate="minlen=5,maxlen=15,regexp='^[\+]?[0-9]+$',msg='Invalid phone number format! Example: 6781234567'">
        </div>
    </div>
    <div class="form-group">
        <div class="col-xs-offset-2 col-xs-8">
            <button id="bsaveprofile" class="btn btn-success btn-lg btn-font">Save</button>
        </div>
    </div>
</div>
<br clear="all" /><br />
