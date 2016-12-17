<?php

function log_error( $num, $str, $file, $line, $context = null )
{    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}

function log_exception($e)
{  $r = new stdClass();
   $r->error = true;
   $r->errmsg = $e->getMessage().' line: '.$e->getLine().' in '.$e->getFile();
   echo json_encode($r);
   exit();
}

function check_for_fatal()
{   $error = error_get_last();
    if ( $error["type"] == E_ERROR )
    log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}

register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "log_exception" );
ini_set( "display_errors", "off" );

?>
