<h2><?=T('Users')?></h2>

<div class="input-group">
    <input id="tsearch" type="text" class="form-control w-stext" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
    <span class="input-group-btn"><button id="btsearch" class="btn btn-default w-search" type="button"><?=T('Search')?></button> </span>
</div>

<div id="users-list">
    <table id="users-table" class="table table-striped selectable">
    <thead>
        <tr><th>Name</th><th>First name</th><th>Last name</th><th>Email</th></tr>
    </thead>
    <tbody>
    </tbody>    
    </table>
    <fieldset id="editform" class="hidden">
        <div id="user-groups"></div>
        <div class="form-group" style="margin-bottom:10px">
        <button class="btn btn-lg btn-info" id="btgrsave" >Save</button>
        <button class="btn btn-lg btn-danger" id="btdelete"><span class="glyphicon glyphicon-trash"></span>&nbsp;Delete</button>
        </div>
    </fieldset>
</div>
<p><b>Users total: <span class="records-total"></span></b></p>


