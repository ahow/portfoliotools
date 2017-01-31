<?php
  $db = $this->db;
  $qr = $db->query("select autorespsubj, autorespbody from templates where name='evnamegl'");
  $r = $db->fetchSingle($qr);
?>
<div class="container">
    <div class="row">
       <div class="col-lg-5">
        <div class="alert" role="alert">
           <h4 class="alert-title"><?=$r->autorespsubj?></h4>
             <p class="alert-message"><?=$r->autorespbody?></p>
       </div>
       </div>  <!-- col-lg-5 --> 
  </div>  <!-- row -->
  <div class="row">
      <div class="col-lg-5"
          <div class="form-group">
            <center><a class="btn btn-info btn-lg btn-font" href="/index.php/guestlist">Next</a></center>
          </div>
      </div>
  </div>
</div>

