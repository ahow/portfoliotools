<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

require SYS_PATH.'lib/mime.php';
require SYS_PATH.'/vendor/autoload.php';

function setRowValues($sheet, $row, $a)
{  foreach($a as $col=>$v) $sheet->setCellValueByColumnAndRow($col+1, $row, $v);
}
 
if ($this->inGroup('admin') || $this->inGroup('editor'))
{
      output_headers('Company-themes.xlsx');                 
      $db = $this->db;
      $h = array('ISIN');
      
      $theams = [];
      $qr=$db->query('select * from sales_theams order by id');
      $i = 0;
      while ($r=$db->fetchSingle($qr))
      {  $theams[$r->id] = (object)['n'=>$i, 'theam'=>$r->theam];
         $i++;
      }

    $qr=$db->query(' select ct.cid, c.name, c.isin,  t.id, ct.theam_value
from sales_company_theams ct 
join sales_theams t on ct.theam_id=t.id
join sales_companies c on ct.cid=c.cid
order by ct.cid, t.id');


    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()->setCreator('Andrew Howard')
    ->setLastModifiedBy('Andrew Howard')
    ->setTitle('Company theme metrics')
    ->setSubject('Company theme metrics')
    ->setDescription('Company theme metrics')
    ->setKeywords('')
    ->setCategory('');

    $spreadsheet->setActiveSheetIndex(0)->setTitle('CompanyThemeMetrics');
    $sheet= $spreadsheet->getActiveSheet();
    $sheet->getColumnDimension('B')->setWidth(35);
    $sheet->getColumnDimension('C')->setWidth(16);
    $sheet->getStyle("1:1")->getFont()->setBold( true );

    $cid = null;
    $a = ['CID','NAME','ISIN'];
    $j = 3;

    foreach ($theams as $th) {
        $a[$j] = $th->theam;
        $j++;
    }
    $i=1;
    setRowValues($sheet,$i++,$a);
    $a = [];

    while ($r=$db->fetchSingle($qr))
    {  if ($cid!==$r->cid)
       {   $cid=$r->cid;
           if (!empty($a)) setRowValues($sheet,$i++,$a);
           $a = [$r->cid, $r->name, $r->isin];
       }
       $a[ $theams[$r->id]->n+3 ] = $r->theam_value;
    }    
    if (!empty($a)) setRowValues($sheet,$i++,$a);

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');

} else  header("HTTP/1.0 404 Not Found");
?>
