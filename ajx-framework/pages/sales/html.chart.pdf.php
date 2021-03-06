<?php
   include(SYS_PATH.'lib/tcpdf.php');  
  
   // create new PDF document
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('Andrew Howard');
   $title = post('title');
   $type = post('type');
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
    $pdf->SetFont('dejavusans', '', 14, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();

    // set text shadow effect
    $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

    // Set some content to print

    // Print text using writeHTMLCell()
    // $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    if ($type=='') $pdf->ImageSVG($file='@'.post('svg'), $x=10, $y=30, $w='', $h=200, $link='', $align='', $palign='', $border=0, $fitonpage=true);
    else if ($type=='pfmetrics')
    {
        $pdf->ImageSVG($file='@'.post('svg1'), $x=10, $y=30, $w=97.5, $h='', $link='', $align='', $palign='', $border=0, $fitonpage=false);
        $pdf->ImageSVG($file='@'.post('svg2'), $x=102.5, $y=30, $w=97.5, $h='', $link='', $align='', $palign='', $border=0, $fitonpage=false);
        $pdf->ImageSVG($file='@'.post('svg3'), $x=10, $y=110, $w=190, $h='', $link='', $align='', $palign='', $border=0, $fitonpage=false);
    } else if ($type=='industry')
    {   // file_put_contents('/tmp/test.svg', post('svg'));
        // $pdf->setRasterizeVectorImages(true);
        $pdf->ImageSVG($file='@'.post('svg'), $x=10, $y=30, $w=190, $h='', $link='', $align='', $palign='', $border=0, $fitonpage=false);
        // $pdf->ImageSVG($file='/tmp/test2.svg', $x=10, $y=130, $w=190, $h='', $link='', $align='', $palign='', $border=0, $fitonpage=false);
    }

    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $file_name = str_replace( array(' ',"\t"), '_', $title).'.pdf';   
    $pdf->Output($file_name, 'I');

?>
