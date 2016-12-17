<div>
    <div class="row">
        <div class="col-lg-4">
             <h1><?=T($this->cfg->title)?></h1>
             <div class="list-group" id="admin-menu">
                    <a href="javascript:" data-view="userlist" class="list-group-item"><?=T('Users')?></a>
                    <a href="javascript:" data-view="changepass" class="list-group-item"><?=T('CHANGE_PASSWORD')?></a>
                    <a href="javascript:" data-view="emailsettings" class="list-group-item"><?=T('EMAIL_SETTINGS')?></a>
             </div>
        </div>
        <div class="col-lg-8" id="views">
        </div>
    </div>
</div>
