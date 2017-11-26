<?php
    include(SYS_PATH.'lib/bsforms.php');
    $f = new BSformDefault();
?>
<h2><?=T('Users')?></h2>

<div class="input-group">
    <input id="tsearch" type="text" class="form-control w-stext" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
    <span class="input-group-btn"><button id="btsearch" class="btn btn-default w-search" type="button"><?=T('Search')?></button> </span>
</div>

<div id="users-list"class="model-list" data-model="/pages/admin/Model/users">
    <table class="table table-striped selectable">
            <thead></thead>
            <tbody></tbody>
    </table>
    <div class="model-pager"></div>
    <fieldset id="editform" class="hidden">
        <div id="user-groups"></div>
        <div class="form-group" style="margin-bottom:10px">
        <button class="btn btn-lg btn-info" id="btgrsave" >Save</button>
        <button class="btn btn-lg btn-danger" id="btdelete"><span class="glyphicon glyphicon-trash"></span>&nbsp;Delete</button>
        </div>
    </fieldset>
</div>
<p><b>Users total: <span class="records-total"></span></b></p>


<!-- Modal -->
<div class="modal fade" role="dialog" id="useradd-form" data-model="/pages/admin/Model/users">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">User</h4>
      </div>
      <div class="modal-body">
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('req,minlen=2'); echo $f->input('name'); ?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('req,minlen=2'); echo $f->input('firstname');?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?=$f->input('lastname')?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('email'); echo $f->input('email')?></div>
            </div>
            <div class="row">
                <div class="col-lg-12"><?=$f->input('phone')?></div>
            </div>           
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('minlen=3'); echo $f->input('pass','password');?></div>
            </div> 
            <div class="row">
                <div class="col-lg-12"><?php $f->validate('minlen=3,equalto=#pass'); echo $f->input('pass2','password');?></div>
            </div> 
            <?=$f->key('id')?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-default btn-success b-useradd">Save</button>
      </div>
    </div>

  </div>
</div>
