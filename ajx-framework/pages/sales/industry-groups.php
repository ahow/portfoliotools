<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>

<h2 class="uppertitle"><?=$this->cfg->title?></h2>

<div class="model-list" data-model="/pages/sales/Model/industry_groups">
    <div class="input-group model-search">
        <input type="text" class="form-control" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
        <span class="input-group-btn">
            <button class="btn btn-default" type="button">
                <span class="glyphicon glyphicon-search"></span>&nbsp;
                <?=T('Search')?>
            </button> 
        </span>
    </div>
    <table class="table table-striped selectable">
        <thead></thead>
        <tbody></tbody>
    </table>
    <div class="model-pager"></div>
    <!--
    <div class="btn-toolbar">
        <a class="btn btn-lg btn-info" href="<?=mkURL('/sales/industry-group-new')?>"><?=T('NEW')?></a>
    </div>
    -->
</div>

