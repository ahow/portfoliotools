<?php
   include('../lib/tcpdf.php');  
  
   // create new PDF document
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('Andrew Howard');
   $title = post('title');
   $pdf->SetTitle($title);
   $pdf->SetSubject($title);
   $pdf->SetKeywords('chart '.$title);

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $title, '', array(0,0,0), array(128,128,128));
    $pdf->setFooterData(array(0,0,0), array(128,128,128));

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 10, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();

    // set text shadow effect
    $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

    // Set some content to print

    // Print text using writeHTMLCell()    

    $d = json_decode(post('data'));
    
    $s = $d->descr.'<br><br><table cellpadding="2" cellspacing="2">
    <tr style="background-color:#E5E5E5;"><th width="200">Company</th><th>% of sales</th><th>-1Yr</th><th>-2Yr</th></tr>';
   //  
    
    function toFloat($n, $p=2)
    {   if (!is_numeric($n)) return '-';
        return number_format($n, $p);
    }
    
    foreach ($d->rows as $k=>$r)
    {   if (isset($r->name)) $s.='<tr><td  width="200">'.$r->name.'</td><td>'.toFloat($r->psale).'</td><td>'.toFloat($r->psaleY1).'</td><td>'.toFloat($r->psaleY2).'</td></tr>';
    }
    $s .= '</table>';
    //$s.= $d->rows[0]->name;
    $pdf->writeHTMLCell(0, 0, '', '', $s, 0, 1, 0, true, '', true);
    
    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $file_name = str_replace( array(' ',"\t"), '_', $title).'.pdf';   
    $pdf->Output($file_name, 'I');

?>
