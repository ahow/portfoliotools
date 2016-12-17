<h2>Email Settings</h2>
<form id="email-settings">
  <div class="form-group">
    <label for="smtphost">SMTP Host</label>
    <input type="text" class="form-control" id="smtphost" placeholder="SMTP Host" value="localhost">
  </div>
  <div class="form-group">
    <label for="smtpport">SMTP Port</label>
    <input type="number" class="form-control" id="smtpport" placeholder="SMTP Port" value="25">
  </div>
  <div class="form-group">
    <label for="smtpuser">SMTP User</label>
    <input type="text" class="form-control" id="smtpuser" placeholder="SMTP User">
  </div>
  <div class="form-group">
    <label for="smtppassword">SMTP Password</label>
    <input type="password" class="form-control" id="smtppassword" placeholder="SMTP Password">
  </div>
  <div class="form-group">
    <label for="smtppassword">SMTP Secure</label>
    <select class="form-control" id="smtpsecure">
        <option value=""></option>
        <option value="tls">TLS</option>
        <option value="ssl">SSL</option>        
    </select>
  </div>
  <div class="form-group">
    <label for="smtpsender">Sender E-mail</label>
    <input type="email" class="form-control" id="smtpsender" placeholder="Sender E-mail">
  </div>
  <div class="form-group">
    <label for="smtpsendername">Sender Name</label>
    <input type="text" class="form-control" id="smtpsendername" placeholder="Sender Name">
  </div>
  <div class="form-group">
    <label for="adminemail">Administrator E-mail</label>
    <input type="email" class="form-control" id="adminemail" placeholder="Administrator E-mail">
  </div>
  <button type="button" class="btn btn-default btn-default btn-warning btn-lg disabled" id="btsmtptest">Send test email</button>  
  <button type="button" class="btn btn-default btn-default btn-info btn-lg" id="btsmtpsave">Save settings</button>
</form>


