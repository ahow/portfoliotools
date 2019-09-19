<?php
/*
    -- Enable
    SET global general_log = on;
    SET global log_output = 'table';

    -- Clear
    SET global general_log = off;
    TRUNCATE table mysql.general_log;
    SET global general_log = on;

*/
    error_reporting(E_ALL);

    $db = new PDO('mysql:host=localhost;', 'boss', ',jcc');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try    
    {   $qr = $db->query('set names utf8');
        $qr = $db->query('use mysql');
        $qr = $db->prepare("select * from mysql.general_log where command_type='Query' ");        
        $qr->execute();
        $i = 0;
        while ($r=$qr->fetch(PDO::FETCH_OBJ))
        { // user_host                 | thread_id | server_id | command_type |  
            echo "-- $r->user_host   thread: $r->thread_id\n";
            echo "$r->argument;\n\n";
        }

    } 
    catch(Exception $err) 
    {
        echo $err->getCode().' '.$err->getMessage()."\n";  
        print_r( $err->getTrace() );      
    }
?>
