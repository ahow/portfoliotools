<?php

    if (!$user) $fullname='Anonymous'; else
    $fullname = $user->firstname.' '.$user->lastname;
 
class BSformDefault
{   var $fa;
    var $jfilt;
    
    function BSformDefault()
    { $this->fa = array();
      $this->jfilt = new stdClass();
    }
    
    function mkTestFilt($name,$filter='number',$maxlen=null,$min=null,$max=null)
    {  $r = new StdClass();
       $r->f = $filter;
       if ($maxlen!==null) $r->maxlen = $maxlen;
       if ($min!==null) $r->min = $min;
       if ($max!==null) $r->max = $max;
       $this->jfilt->$name = $r;
    }
    
    function input($name,$type='text',$size=220)
    {  $label = T($name);
       
       $this->mkTestFilt($name,$type,$size);
       $this->fa[] = "$name VARCHAR($size)";
       
       return ' <div class="form-group">
            <label for="'.$name.'">'.$label.'</label>
            <input type="'.$type.'" class="form-control" id="'.$name.'" placeholder="'.$label.'">
          </div>';
    }
    
    function date($name,$placeholder='11/28/2016')
    {   $label = T($name);
        $this->fa[] = "$name DATE";
        $this->mkTestFilt($name,'date');
        return '<div class="form-group">
        <label for="'.$name.'">'.$label.'</label>
        <div class="input-group date" id="'.$name.'">          
          <input type="date" class="form-control" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
        </div>
      </div>';
    }

    function period($name,$d1,$d2,$placeholder='11/28/2016')
    {   $label = T($name);
        $this->fa[] = "$name DATE";
        $this->mkTestFilt($name,'date');
        return '<div class="form-group">
        <label for="'.$name.'">'.$label.'</label>
        <div class="input-group date" id="'.$name.'">          
          <input type="date" class="form-control" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
          <input type="date" class="form-control" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
        </div>
      </div>';
    }
    
    function between($name1,$name2,$title,$holder1,$holder2)
    {   $this->fa[] = "$name1 SMALLINT";
        $this->fa[] = "$name2 SMALLINT";
        
        $this->mkTestFilt($name1,'number');
        $this->mkTestFilt($name2,'number');
        
        return '<div class="form-group">
        <label>'.T($title).'</label>
        <div class="input-group">
          <input type="number" class="form-control" id="'.$name1.'" placeholder="'.$holder1.'">
          <input type="number" class="form-control" id="'.$name2.'" placeholder="'.$holder2.'">
        </div>
      </div>';
    }
    
    function chbox($name)
    {  $label = T($name);
       $this->fa[] = "$name BOOLEAN";
       $this->mkTestFilt($name,'number',null,0,1);
        return '<div class="form-group checkbox">
    <label><input type="checkbox" id="'.$name.'" />'.$label.'</label>
  </div>';
    }

    function textarea($name, $rows=3)
    {   $label = T($name);
        $this->fa[] = "$name TEXT";
        $this->mkTestFilt($name,'text');
         // <label for="comment">'.$label.'</label>
        return '<div class="form-group">
  <textarea class="form-control" rows="'.$rows.'" id="'.$name.'" " placeholder="'.$label.'"></textarea>
</div>';
    }
    
    function btlist($name,$values)
    { $this->fa[] = "$name SMALLINT";
      $this->mkTestFilt($name,'number',null,1,3);
      $s = '<div class="row form-group bradio" id="'.$name.'" data-value=""><div class="col-lg-6 checkbox">';
      $s.= '<label>'.T($name).'</label></div>';
      $s.='<div class="btn-group col-lg-6" role="group">';
      $list=explode(',',$values);
      foreach($list as $i=>$l)
      {   $s .= '<button type="button" data-id="'.($i+1).'" class="btn btn-default">'.T($l).'</button>';          
      }
      $s.= '</div></div>';
      return $s;
    }
    
    function tabs($name)
    {   return '<tr><td><label>'.T($name).'</label></td><td><input type="checkbox" /></td><td><input type="checkbox" /></td><td><input type="checkbox" /></td></tr>';
    }
    
    function printFields()
    { // echo implode(",\n", $this->fa);
      echo json_encode($this->jfilt);
    }
}

$f = new BSformDefault();
$gt = T('GREATER_THAN');
$lt = T('LESS_THAN');
?>
<div id="hha_care_plan">
  <div class="form-group">
    
  </div>
  <div class="row">
      <div class="col-lg-3"><h3><?=$fullname?></h3></div>
      <div class="col-lg-3"><?=$f->input('mrn')?></div>
      <div class="col-lg-3"><?=$f->date('visit_date')?></div>
      <div class="col-lg-3"><?=$f->btlist('dnr','Yes,No')?></div>
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
                      <!--
                        <div class="row divider"><?=T('VITAL_SIGNS')?></div>
                        <div class="row"><?=$f->btlist('ptemperature','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('pblood_pressure','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('pheart_rate','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('prespirations','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('pweight','QV,QW,NA')?></div>
                      -->
                       <table class="table">
                           <tr><th><center><?=T('VITAL_SIGNS')?></center></th><th>QV</th><th>QW</th><th>N/A</th></tr>
                           <?php 
                           echo $f->tabs('ptemperature');
                           echo $f->tabs('pblood_pressure');
                           echo $f->tabs('pheart_rate');
                           echo $f->tabs('prespirations');
                           echo $f->tabs('pweight');
                           ?>
                       </table>
                  </div>
                  <div class="col-lg-6">
                        <div class="row divider"><?=T('Eliminations')?></div>
                        <div class="row"><?=$f->btlist('assist_w_bed','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_bsc','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('incontinence_care','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('empty_drainage_bag','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('record_bowel_movement','QV,QW,NA')?></div>                  
                        <div class="row"><?=$f->btlist('catheter_care','QV,QW,NA')?></div>                  
                  </div>
                </div> 

                <div class="row">
                  <div class="col-lg-6">
                        <div class="row divider"><?=T('PERSONAL_CARE')?></div>
                        <div class="row"><?=$f->btlist('bed_bath','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_chair_bath','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('tub_bath','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('shower','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('shower_w_chair','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('shampoo_hair','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('hair_care','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('oral_care','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('skin_care','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('pericare','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('nail_care','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('shave','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_dressing','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('medication_remmder','QV,QW,NA')?></div>
                  </div>
                  <div class="col-lg-6">
                        <div class="row divider"><?=T('Activity')?></div>
                        <div class="row"><?=$f->btlist('dangle_on_side_bed','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('turn_and_position','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_transfer','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('range_of_motion','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_ambulation','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('equipment_care','QV,QW,NA')?></div>
                        
                        <div class="row divider"><?=T('HOUSEHOLD_TASK')?></div>
                        <div class="row"><?=$f->btlist('make_bed','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('change_linen','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('light_housekeeping','QV,QW,NA')?></div>

                        <div class="row divider"><?=T('Nutrition')?></div>
                        <div class="row"><?=$f->btlist('meal_setup','QV,QW,NA')?></div>
                        <div class="row"><?=$f->btlist('assist_w_feeding','QV,QW,NA')?></div>
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
                  <div class="col-lg-4"><?=$f->chbox('paralisys')?></div>
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
  if (isset($this->seg[2]))
  {
      echo '<pre>';
      $f->printFields();
      echo '</pre>';
  }
?>

