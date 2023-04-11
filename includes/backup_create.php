<?php
/********************************************************************************
 * #                      Advanced File Manager v3.0
 * #******************************************************************************
 * #      Author:     Convergine.com
 * #      Email:      info@convergine.com
 * #      Website:    http://www.convergine.com
 * #
 * #
 * #      Version:    3.0
 * #      Copyright:  (c) 2009 - 2015 - Convergine.com
 * #
 * #*******************************************************************************/
require_once("dbconnect.php"); //Load the settings
require_once("functions.php"); //Load the functions
if($demo){
    echo "Operation not permitted in preview of the product.";
} else {
    $backup_directory = "backup_db/";
    $files = glob($backup_directory . "*.sql");
    foreach ($files as $file) {
        unlink($file);
    }
    getBackup($db_host, $db_user, $db_password, $db_name);
    $backup_directory = "backup_db/";
    $dbfile = array();
    $files = glob($backup_directory . "*.sql");
    $i = 0;
    foreach ($files as $file) {
        $dbfile[] = $file;
        $i++;
    }

    rsort($dbfile);
    echo "includes/" . $dbfile[0];
}


?>