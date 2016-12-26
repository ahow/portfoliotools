<ol class="breadcrumb">
  <li><a href="<?=mkURL('/sales')?>"><?=T('Sales')?></a></li>
  <li class="active" ><?=$this->cfg->title?></li>
</ol>

<h2 class="uppertitle"><?=$this->cfg->title?></h2>

<?php
    include(__DIR__.'/bsforms.php');
    $f = new BSformDefault();
?>

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#tabsearh">Search</a></li>
  <li id="tbedit" class="disabled"><a data-toggle="tab" href="#tabedit">Edit</a></li>
</ul>

<div class="tab-content"  style="padding-top: 15px;">
    
    <div id="tabsearh" class="tab-pane fade in active">
        
        <div class="model-list" data-model="/pages/sales/Model/companies">
            <div class="row">
                <div class="col-lg-12">
                    <div class="input-group model-search">
                        <input type="text" class="form-control" data-toggle="tooltip" data-placement="top" title="Search" placeholder="<?=T('Search')?>">
                        <span class="input-group-btn">
                            <button class="btn btn-default b-search" type="button">
                                <span class="glyphicon glyphicon-search"></span>&nbsp;
                                <?=T('Search')?>
                            </button> 
                            <button class="btn btn-default b-clean" type="button">
                                <span class="glyphicon glyphicon glyphicon-remove"></span>&nbsp;
                                <?=T('Clean')?>
                            </button> 
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4"><?=$f->modelSelect('subsector','/pages/sales/Model/company_subsector')?></div>
                <div class="col-lg-4"><?=$f->modelSelect('industry_group','/pages/sales/Model/company_ig')?></div>
                <div class="col-lg-4"><?=$f->modelSelect('division','/pages/sales/Model/company_division')?></div>
            </div>
            <div class="row">
                <div class="col-lg-4"><?=$f->modelSelect('major_group','/pages/sales/Model/ig_major_group')?></div>
                <div class="col-lg-4"><?=$f->search3dot('sic_code','sic')?></div>
                <div class="col-lg-4"><?=$f->modelSelect('fregion','/pages/sales/Model/regions')?></div>
            </div>
            <table class="table table-striped selectable">
                <thead></thead>
                <tbody></tbody>
            </table>
            <div class="model-pager"></div>
        </div>
        
    </div>
    
    <div class="tab-pane fade" id="tabedit">

        <!--  -->
        <div id="form1">
           <div id="company-data" data-model="/pages/sales/Model/companies">
                <div class="row">
                    <div class="col-lg-6"><?=$f->input('name')?></div>
                    <div class="col-lg-4"><?=$f->input('isin')?></div>
                    <div class="col-lg-2"><?=$f->input('year','number')?></div>
                </div>
                <div class="row">
                    <div class="col-lg-6"><?=$f->input('sector')?></div>
                    <div class="col-lg-4"><?=$f->input('region')?></div>
                    <div class="col-lg-2"><?=$f->chbox('reviewed')?></div>
                    <!-- <div class="col-lg-2"><?=$f->input('minsize','number')?></div> -->
                </div>
                <?=$f->key('cid')?>
            </div>
            <div class="row formdata">
            </div>        
        </div>
        <div id="search_company"></div>
        <div class="button-group">
            <button class="btn btn-lg btn-success hidden disabled b-edit-div">Edit Division</button>
        </div>        
        
    </div>

</div>
<div id="search_sic"></div>
<div id="search_sic2"></div>
