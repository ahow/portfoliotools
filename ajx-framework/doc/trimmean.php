<?php


function TRIMMEAN($aArgs, $percent) 
{
    function average($a)
    { $s = 0;
      foreach($a as $v) $s+=$v;
      return $s/count($a);
    }

    
    if ((is_numeric($percent)) && (!is_string($percent))) 
    {   if (($percent < 0) || ($percent > 1)) return false;       
        $mArgs = array();
        foreach ($aArgs as $arg) {
          // Is it a numeric value?
          if ((is_numeric($arg)) && (!is_string($arg))) {
            $mArgs [] = $arg;
          }
        }
        $discard = floor(count($mArgs) * $percent / 2);
        sort($mArgs);
        for ($i = 0; $i < $discard; ++$i) {
          array_pop($mArgs);
          array_shift($mArgs);
        }
        return average($mArgs);
  }
  return PHPExcel_Calculation_Functions::VALUE();
}

$a = array(10,2,3,5,4,7,1,9,3,5,9);

echo TRIMMEAN($a, 0.3);

?>
