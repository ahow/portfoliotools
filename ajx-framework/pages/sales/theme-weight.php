<?php
    $this->displayCrumbs();
    $db = $this->cfg->db;
    $qr = $db->query("select * from sales_theams");
?>
<div class="row">
 <div class="col-lg-8 col-md-10 col-sm-12" >
    <table class="table table-striped">
        <thead>
            <th>Theme</th>
            <th>Company weight</th>
            <th>SIC weight</th>
        </thead>
        <tbody class="w-weight-list">
            <?php
                while ($r = $db->fetchSingle($qr)) 
                {
                    echo '<tr data-id="'.$r->id.'">';
                    echo '<td>'.$r->theam.'</td>';
                    echo '<td class="w-comp-weight" contenteditable="true">'.$r->company_weight.'</td>';
                    echo '<td>'.$r->SIC_weight.'</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
 </div> 
</div>
<div class="row" style="margin-bottom: 50pt">
    <div class="col-lg-8 col-md-10 col-sm-12" >
        <button class="btn btn-primary btn-lg pull-right w-bsave" type="button"><?=T('Save')?></button>
    </div>
</div>



