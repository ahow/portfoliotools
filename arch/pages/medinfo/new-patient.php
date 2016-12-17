<?php

include(__DIR__.'/bsforms.php');
 
if (!$user) $fullname='Anonymous'; else
$fullname = $user->firstname.' '.$user->lastname;
 
$f = new BSformDefault();
$gt = T('GREATER_THAN');
$lt = T('LESS_THAN');
?>

<div id="hha_patient" class="model-form" style="max-width:600px" data-model="/pages/medinfo/Model/patients" data-if-inserted-redirect="<?=mkURL('/medinfo/patients')?>">
  <div class="form-group">
    
  </div>
  <div class="row">
      <div class="col-lg-12">
          <?=$f->date('date_of_birth')?>
          <?=$f->input('firstname')?>
          <?=$f->input('lastname')?>
          <?=$f->input('email','email')?>
      </div>
  </div>
  
  <a  class="btn btn-warning btn-lg" href="<?=mkURL('/medinfo/patients')?>">CANCEL</a>
  <button type="button" class="btn btn-success btn-lg model-insert">SAVE</button>
</div>

<br clear="all" /> <br />

<?php
  if (isset($this->seg[2]))
  {
      echo '<pre>';
      $f->printFields();
      echo '</pre>';
  }
?>
