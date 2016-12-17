<div class="model-list" data-model="/pages/medinfo/Model/patients">
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
    <div class="btn-toolbar">
        <a class="btn btn-lg btn-info" href="<?=mkURL('/medinfo/new-patient')?>"><?=T('NEW_PATIENT')?></a>
        <a class="btn btn-lg btn-info hidden" id="btcareplans" href="#"><?=T('PATIENT_CARE_PLANS')?></a>
        <a class="btn btn-lg btn-success disabled" id="btnewcareplan" href="#"><?=T('NEW_CARE_PLAN')?></a>
    </div>
</div>

