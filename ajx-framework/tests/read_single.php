<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/../vendor/autoload.php';

function do_log($m) { echo $m."\n"; }

$inputFileType = 'Xlsx';
// $inputFileName = '/home/fvv/upwork/Andrew Howard/All_data.xlsx';
$inputFileName = '../../uploads/company-20190922184837.8203.xlsx';
$sheetname = 'DivisionDetails';

do_log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory with a defined reader type of ' . $inputFileType);
$reader = IOFactory::createReader($inputFileType);
do_log('Loading Sheet "' . $sheetname . '" only');
$reader->setLoadSheetsOnly($sheetname);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($inputFileName);

do_log($spreadsheet->getSheetCount() . ' worksheet' . (($spreadsheet->getSheetCount() == 1) ? '' : 's') . ' loaded');
$loadedSheetNames = $spreadsheet->getSheetNames();
foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
    do_log($sheetIndex . ' -> ' . $loadedSheetName);
    /*
    $data = $spreadsheet->getSheet($sheetIndex)->toArray();
    print_r($data[0]);
    print_r($data[1]);
    */
}
