<?php
 
 class BSformDefault
{   var $fa;
    var $jfilt;
    
    function __construct()
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
    
    function search($id,$name)
    {  $label = T($name);
       return '<div class="form-group"><label for="'.$name.'">'.$label.'</label>
       <div class="input-group form-lookup" data-control-type="data" id="'.$id.'" data-value="">
        <input type="text" class="form-control" data-control-type="basic" id="'.$name.'" data-toggle="tooltip" data-placement="top" title="'.$label.'" placeholder="'.$label.'">
        <span class="input-group-btn">
            <button class="btn btn-default" type="button">
                <span class="glyphicon glyphicon-search"></span>&nbsp;'.T('Search').'
            </button> 
        </span>
    </div></div>';
    }

    function search3dot($id,$name)
    {  $label = T($name);
       return '<div class="form-group"><label for="'.$name.'">'.$label.'</label>
       <div class="input-group form-lookup" data-control-type="data" id="'.$id.'" data-value="">
        <input type="text" class="form-control" data-control-type="basic" id="'.$name.'" data-toggle="tooltip" data-placement="top" title="'.$label.'" placeholder="'.$label.'">
        <span class="input-group-btn">
            <button class="btn btn-default" type="button">...</button> 
        </span>
    </div></div>';
    }
    
    function input($name,$type='text',$size=220)
    {  $label = T($name);
       
       $this->mkTestFilt($name,$type,$size);
       $this->fa[] = "$name VARCHAR($size)";
       
       return ' <div class="form-group">
            <label for="'.$name.'">'.$label.'</label>
            <input type="'.$type.'" class="form-control" data-control-type="basic" id="'.$name.'" placeholder="'.$label.'">
          </div>';
    }

    function label($name)
    {  // $label = T($name);       
       return ' <div class="form-group">
            <label id="'.$name.'"></label>            
          </div>';
    }
    
    function hidden($name, $value, $type='INTEGER')
    {  $label = T($name);       
       $this->mkTestFilt($name,$type);
       $this->fa[] = "$name $type";
       return '<input type="hidden" class="form-control" data-control-type="basic" id="'.$name.'">';
    }

    function key($name, $value='')
    {  $label = T($name);       
       $type = 'INTEGER';
       $this->mkTestFilt($name, $type);
       $this->fa[] = "$name $type";
       return '<input type="hidden" class="form-control" data-key="true" data-control-type="basic" id="'.$name.'">';
    }
    
    function date($name,$placeholder='11/28/2016')
    {   $label = T($name);
        $this->fa[] = "$name DATE";
        $this->mkTestFilt($name,'date');
        return '<div class="form-group">
        <label for="'.$name.'">'.$label.'</label>
        <div class="input-group date">          
          <input type="date" data-control-type="basic" id="'.$name.'" class="form-control" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
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
          <input type="date" class="form-control" data-control-type="basic" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
          <input type="date" class="form-control" data-control-type="basic" name="bookdate" placeholder="'.$placeholder.'"  data-validate="req,regexp=\'^(0?[1-9]|1[012])[\/](0?[1-9]|[12][0-9]|3[01])[\/\-]([0-9]{4})$\',msg=\'Invalid date format!\'"/>
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
          <input type="number" data-control-type="basic" class="form-control" id="'.$name1.'" placeholder="'.$holder1.'">
          <input type="number" data-control-type="basic" class="form-control" id="'.$name2.'" placeholder="'.$holder2.'">
        </div>
      </div>';
    }
    
    function chbox($name)
    {  $label = T($name);
       $this->fa[] = "$name BOOLEAN";
       $this->mkTestFilt($name,'number',null,0,1);
        return '<div class="form-group checkbox">
    <label><input type="checkbox" data-control-type="checkbox" id="'.$name.'" />'.$label.'</label>
  </div>';
    }

    function textarea($name, $rows=3)
    {   $label = T($name);
        $this->fa[] = "$name TEXT";
        $this->mkTestFilt($name,'text');
         // <label for="comment">'.$label.'</label>
        return '<div class="form-group">
  <textarea class="form-control" rows="'.$rows.'" id="'.$name.'" "  data-control-type="basic" placeholder="'.$label.'"></textarea>
</div>';
    }
    
    function btlist($name,$values)
    { $this->fa[] = "$name SMALLINT";
      $this->mkTestFilt($name,'number',null,1,3);
      $s = '<div class="row form-group bradio" id="'.$name.'"  data-control-type="data" data-value=""><div class="col-lg-6 checkbox">';
      $s.= '<label>'.T($name).'</label></div>';
      $s.='<div class="btn-group col-lg-6" role="group">';
      $list=explode(',',$values);
      foreach($list as $i=>$l)
      {   $s .= '<button type="button" data-id="'.($i+1).'" class="btn btn-default">'.T($l).'</button>';          
      }
      $s.= '</div></div>';
      return $s;
    }
    
    function modelSelect($name,$model)
    {  $label = T($name);
       $s = '<div class="form-group bs-model-select" data-model="'.$model.'">
  <label for="'.$name.'">'.$label.'</label>
  <select class="form-control" id="'.$name.'" data-control-type="basic">
  </select>
</div>';
       return $s;
    }
    
    function listSelect($name,$list)
    {  $label = T($name);
       $a = explode(';', $list);
       $s = '<div class="form-group bs-model-select" data-model="'.$model.'">
  <label for="'.$name.'">'.$label.'</label>
  <select class="form-control" id="'.$name.'" data-control-type="basic">';
       foreach($a as $k=>$v)
       { $s.='<option value="'.($k+1).'">'.$v.'</option>';
       }
       $s.='</select>
</div>';
       return $s;
    }
    
    function vwtabs($name)
    {     $qv = $name."_qv";
          $qw = $name."_qw";
          $this->fa[] = "$qv BOOLEAN";
          $this->fa[] = "$qw BOOLEAN";
          return '<tr class="vwtabs"><td><label>'.T($name).'</label></td>
          <td><input id="'.$qv.'" type="checkbox" data-control-type="basic" /></td>
          <td><input data-control-type="basic" id="'.$qw.'" type="checkbox" /></td>
          <td><input type="checkbox" class="vwtabs-na" /></td></tr>';
    }
    
    
    function printFields()
    { // echo implode(",\n", $this->fa);
      echo json_encode($this->jfilt);
    }
}

?>
