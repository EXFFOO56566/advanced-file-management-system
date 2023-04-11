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
ob_start();
session_start();
require_once("dbconnect.php"); //Load the settings
require_once("functions.php"); //Load the functions
set_time_limit(0);

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true) {
    header("Location: index.php");
    exit();
} else {
    //get access level
    if (isset($_SESSION["accesslevel"])) {
        $access = $_SESSION["accesslevel"];
        //determin admin or not.
        if (stristr($access, "abcdef")) {
            $level = "admin";
        } else {
            $level = "user";
        }
    }
    if ($level == "admin") {
        $i          = 0;
        $csv_output = "";
        $result = mysqli_query($mysqli,"SHOW COLUMNS FROM {$db_pr}activitylogs");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $csv_output .= $row['Field'] . ", ";
                $i++;
            }
        }
        $csv_output .= "\n";
        $values = mysqli_query($mysqli,"SELECT * FROM {$db_pr}activitylogs ORDER BY date DESC");
        while ($rowr = mysqli_fetch_row($values)) {
            for ($j = 0; $j < $i; $j++) {
                $csv_output .= $rowr[$j] . ", ";
            }
            $csv_output .= "\n";
        }
        $filename = "activityLogs[" . date("Y-m-d_H-i", time()) . "]";
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m-d") . ".csv");
        header("Content-disposition: filename=" . $filename . ".csv");
        print $csv_output;
        exit;
    } else {
        header("Location: index.php");
    }
}
?>

