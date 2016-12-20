<?php

$gl_errors = array();

function log_error( $num, $str, $file, $line, $context = null )
{    log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
}

function log_exception($e)
{
    echo '<div class="alert alert-danger">';
    echo $e->getMessage().' line: '.$e->getLine().' in '.$e->getFile();
    echo '</div>'; 
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
