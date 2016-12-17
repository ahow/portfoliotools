<?php

include(__DIR__.'/bsforms.php');

$fullname='';

if (!isset($this->seg[2]))
{ echo '<div class="alert alert-warning"><b>'.T("PATIENT_NOT_SELECTED").
    '</b> <a href="'.mkURL('/medinfo/patients').'">'.T("SELECTED_PATIENT").'</a></div>';
   die();
} else
{ $qr = $db->query('select id, firstname, lastname from hha_patients where id=:id',
    array('id'=>$this->seg[2]));
  $partient = $db->fetchSingle($qr);
  if (empty($partient))
  {  echo '<div class="alert alert-danger"><b>'.T("PATIENT_NOT_FOUND").'</div>';
  } else  $fullname = $partient->firstname.' '.$partient->lastname;
}
 
$f = new BSformDefault();
$gt = T('GREATER_THAN');
$lt = T('LESS_THAN');

if ($fullname!='')
{

?>
<div id="hha_care_plan">
  <div class="form-group">
    
  </div>
  <div class="row">
      <div class="col-lg-3"><h3><?php echo $fullname; $f->hidden('patient_id',$partient->id) ?></h3></div>
      <div class="col-lg-3"><?=$f->input('mrn')?></div>
      <div class="col-lg-3"><?=$f->date('visit_date')?></div>
      <div class="col-lg-3">
          <div class="form-group" for="dnr">
          <label>DNR</label>
          <div id="dnr">
          <label class="radio-inline"><input type="radio" name="optradio">Yes</label>
          <label class="radio-inline"><input type="radio" name="optradio">No</label>
          </div>
          </div>
      </div>
  </div>
  <div class="row">
      <div class="col-lg-6"><?=$f->period('episode_period','d1','d2')?></div>
      <div class="col-lg-6"><?=$f->input('hha_frequency')?></div>
  </div>
  <div class="row">
      <div class="col-lg-6"><?=$f->input('primary_diagnosis')?></div>
      <div class="col-lg-6"><?=$f->input('secondary_diagnosis')?></div>
  </div>      
  <div class="row">
      <div class="col-lg-6"><?=$f->input('diet')?></div>
      <div class="col-lg-6"><?=$f->input('allergies')?></div>
  </div>
  
  <div class="row">
     
     <div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
            <div class="row">
                <div class="col-lg-4 panel-title">
                    <h4><a data-toggle="collapse" href="#collapse1"><?=T('VITAL_SIGN_PARAMS')?></a></h4>
                </div>   
                <div class="col-lg-8">
                    <?=$f->chbox('vital_sign_na')?>
                </div>
            </div>
          </div>
          <div id="collapse1" class="panel-collapse collapse in">
            <div class="panel-body">
                  <div class="col-lg-2"><?=$f->between('spb_g','spb_l','SBP',$gt,$lt)?></div>
                  <div class="col-lg-2"><?=$f->between('dpb_g','dpb_l','DBP',$gt,$lt)?></div>
                  <div class="col-lg-2"><?=$f->between('hr_g','hr_l','HR',$gt,$lt)?></div>
                  <div class="col-lg-2"><?=$f->between('resp_g','resp_l','Resp',$gt,$lt)?></div>
                  <div class="col-lg-2"><?=$f->between('temp_g','temp_l','Temp',$gt,$lt)?></div>
                  <div class="col-lg-2"><?=$f->between('weight_g','weight_l','Weight',$gt,$lt)?></div>
            </div>
          </div>
        </div>
      </div>
      
  </div>
  <div class="row">
     
     <div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
              <h4><a data-toggle="collapse" href="#collapse2"><?=T('SAFETY_PRECAUTIONS')?></a></h4>
          </div>
          <div id="collapse2" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('anticoagulant_precautions')?></div>
                  <div class="col-lg-4"><?=$f->chbox('emergency_plan_developed')?></div>
                  <div class="col-lg-4"><?=$f->chbox('fall_precautions')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('keep_pathway_clear')?></div>
                  <div class="col-lg-4"><?=$f->chbox('keep_side_rails_up')?></div>
                  <div class="col-lg-4"><?=$f->chbox('neutropenic_precautions')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('o2_precautions')?></div>
                  <div class="col-lg-4"><?=$f->chbox('proper_position_during_meals')?></div>
                  <div class="col-lg-4"><?=$f->chbox('safety_in_adls')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('seizure_precautions')?></div>
                  <div class="col-lg-4"><?=$f->chbox('sharps_safety')?></div>
                  <div class="col-lg-4"><?=$f->chbox('slow_position_change')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('standard_infect_control')?></div>
                  <div class="col-lg-4"><?=$f->chbox('support_during_transfer')?></div>
                  <div class="col-lg-4"><?=$f->chbox('use_assistive_devices')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-12"><?=$f->textarea('sp_other')?></div>
                </div> 
            </div>
          </div>
        </div>
      </div>
 
     <div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
              <h4><a data-toggle="collapse" href="#collapse3"><?=T('PLAN_DETAILS')?></a></h4>
          </div>
          <div id="collapse3" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="row">
                  <div class="col-lg-6">
                       <table class="table">
                           <tr><th><center><?=T('VITAL_SIGNS')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                           <?php 
                           echo $f->vwtabs('ptemperature');
                           echo $f->vwtabs('pblood_pressure');
                           echo $f->vwtabs('pheart_rate');
                           echo $f->vwtabs('prespirations');
                           echo $f->vwtabs('pweight');
                           ?>
                       </table>
                  </div>
                  <div class="col-lg-6">
                       <table class="table">
                        <tr><th><center><?=T('Eliminations')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                        <?php 
                        echo $f->vwtabs('assist_w_bed');
                        echo $f->vwtabs('assist_w_bsc');
                        echo $f->vwtabs('incontinence_care');
                        echo $f->vwtabs('empty_drainage_bag');
                        echo $f->vwtabs('record_bowel_movement');
                        echo $f->vwtabs('catheter_care');
                        ?>
                       </table>
                  </div>
                </div> 

                <div class="row">
                  <div class="col-lg-6">
                      <table class="table">
                        <tr><th><center><?=T('PERSONAL_CARE')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                        <?php 
                        echo $f->vwtabs('bed_bath');
                        echo $f->vwtabs('assist_w_chair_bath');
                        echo $f->vwtabs('tub_bath');
                        echo $f->vwtabs('shower');
                        echo $f->vwtabs('shower_w_chair');
                        echo $f->vwtabs('shampoo_hair');
                        echo $f->vwtabs('hair_care');
                        echo $f->vwtabs('oral_care');
                        echo $f->vwtabs('skin_care');
                        echo $f->vwtabs('pericare');
                        echo $f->vwtabs('nail_care');
                        echo $f->vwtabs('shave');
                        echo $f->vwtabs('assist_w_dressing');
                        echo $f->vwtabs('medication_reminder');
                        ?>
                       </table>
                  </div>
                  <div class="col-lg-6">
                      <table class="table">
                        <tr><th><center><?=T('Activity')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                        <?php 
                        echo $f->vwtabs('dangle_on_side_bed');
                        echo $f->vwtabs('turn_and_position');
                        echo $f->vwtabs('assist_w_transfer');
                        echo $f->vwtabs('range_of_motion');
                        echo $f->vwtabs('assist_w_ambulation');
                        echo $f->vwtabs('equipment_care');
                        ?>                        
                       </table>
                       <table class="table">
                        <tr><th><center><?=T('HOUSEHOLD_TASK')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                        <?php 
                        echo $f->vwtabs('make_bed');
                        echo $f->vwtabs('change_linen');
                        echo $f->vwtabs('light_housekeeping');
                         ?> 
                       </table>
                       <table class="table">
                        <tr><th><center><?=T('Nutrition')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                        <?php 
                        echo $f->vwtabs('meal_setup');
                        echo $f->vwtabs('assist_w_feeding');
                         ?>                         
                       </table>
                  </div>
                </div> 

            </div>
          </div>
        </div>
      </div>
      
     <div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
              <h4><a data-toggle="collapse" href="#collapse4"><?=T('FUNCTIONAL_LIMITATIONS')?></a></h4>
          </div>
          <div id="collapse4" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('amputation')?></div>
                  <div class="col-lg-4"><?=$f->chbox('bower_bladder')?></div>
                  <div class="col-lg-4"><?=$f->chbox('contracture')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('hearing')?></div>
                  <div class="col-lg-4"><?=$f->chbox('paralysis')?></div>
                  <div class="col-lg-4"><?=$f->chbox('endurance')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('ambulation')?></div>
                  <div class="col-lg-4"><?=$f->chbox('speech')?></div>
                  <div class="col-lg-4"><?=$f->chbox('legally_blind')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('dyspnea_w_min_exertion')?></div>
                  <div class="col-lg-4"><?=$f->chbox('fl_other')?></div>
                </div> 
                
            </div>
          </div>
        </div>
     </div>
           
     <div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
              <h4><a data-toggle="collapse" href="#collapse5"><?=T('ACTIVITIES_PERMITTED')?></a></h4>
          </div>
          <div id="collapse5" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('complete_bed_rest')?></div>
                  <div class="col-lg-4"><?=$f->chbox('bed_rest_w_brp')?></div>
                  <div class="col-lg-4"><?=$f->chbox('up_as_tolerated')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('transfer_bed_chair')?></div>
                  <div class="col-lg-4"><?=$f->chbox('exercise_prescribed')?></div>
                  <div class="col-lg-4"><?=$f->chbox('partial_weight_bearing')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('independent_at_home')?></div>
                  <div class="col-lg-4"><?=$f->chbox('crutches')?></div>
                  <div class="col-lg-4"><?=$f->chbox('cane')?></div>
                </div> 
                <div class="row">
                  <div class="col-lg-4"><?=$f->chbox('wheelchair')?></div>
                  <div class="col-lg-4"><?=$f->chbox('walker')?></div>
                  <div class="col-lg-4"><?=$f->chbox('ap_other')?></div>
                </div> 
                
            </div>
          </div>
        </div>
     </div>
           

  </div>

  <?php
    //echo finput('primary_diagnosis');
  ?>
  
  <button type="button" class="btn btn-success btn-lg" id="bthhasave">SAVE</button>
</div>

<br clear="all" /> <br />

<?php
}
/*
  if (isset($this->seg[2]))
  {
      echo '<pre>';
      $f->printFields();
      echo '</pre>';
  }
  */
?>

