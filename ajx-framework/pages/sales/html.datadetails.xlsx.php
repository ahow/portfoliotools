<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

require SYS_PATH.'lib/mime.php';
require SYS_PATH.'vendor/autoload.php';


function setRowValues($sheet, $row, $a)
{  foreach($a as $col=>$v) $sheet->setCellValueByColumnAndRow($col+1, $row, $v);
}
 
if ($this->inGroup('admin') || $this->inGroup('editor'))
{
    output_headers('Division-Details.xlsx');
    $db = $this->db;                  
      
    $syear = date('Y');
    
    $qr=$db->query('select d.division, d.cid, c.name, d.syear, d.me, d.sic, d.sales, '
      .' d.ebit, d.assets, d.capex'
      .' from sales_divdetails d '
      .' join sales_companies c on d.cid = c.cid '
      .' where d.syear=:syear order by 2,1', ['syear'=>$syear]);      

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()->setCreator('Andrew Howard')
    ->setLastModifiedBy('Andrew Howard')
    ->setTitle('Division details')
    ->setSubject('Division details')
    ->setDescription('Division details')
    ->setKeywords('')
    ->setCategory('');

    $spreadsheet->setActiveSheetIndex(0)->setTitle('DivisionDetails');
    $sheet= $spreadsheet->getActiveSheet();    
    $sheet->getColumnDimension('C')->setWidth(25);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getStyle("1:1")->getFont()->setBold( true );

    $cid = null;
    $a = ['Division','CID','Company name','Name','SIC','SIC Sales','EBIT','Assets','Capex'];
    
    $i=1;
    setRowValues($sheet,$i++,$a);
    $a = [];
    
    while ($r=$db->fetchSingle($qr))
    {   $a[0] = $r->division;
        $a[1] = $r->cid;
        $a[2] = $r->name;
        $a[3] = $r->me;
        $a[4] = $r->sic;
        $a[5] = $r->sales;
        $a[6] = $r->ebit;
        $a[7] = $r->assets;
        $a[8] = $r->capex;
        setRowValues($sheet,$i++,$a);       
    }    
    
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');

} else  header("HTTP/1.0 404 Not Found");
?>