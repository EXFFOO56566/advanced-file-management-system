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
 * #      Copyright:  (c) 2009 - 2014 - Convergine.com
 * #
 * #*******************************************************************************/
require_once("../includes/dbconnect.php"); //Load the settings
$ids = isset($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : array();
$msg = $msgSuccess = '';
if($demo){
    echo(json_encode(array("mes" => "<div class='loginMessage loginError'>Extension deletion not allowed in preview version</div>", "mesSuc" => '')));
} else {
    foreach ($ids as $id) {
        $res = mysqli_query($mysqli, "SELECT *  FROM {$db_pr}extensions WHERE id='" . $id . "' LIMIT 1");
        $extData = mysqli_fetch_assoc($res);
        $sql = "SELECT * FROM {$db_pr}users WHERE extensions LIKE '%{$extData['name']}%' ";
        //print $sql;
        $res = mysqli_query($mysqli, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $usrExt = explode(',', $row['extensions']);
            $_usrExt = array_flip($usrExt);
            unset($_usrExt[$extData['name']]);
            mysqli_query($mysqli, "UPDATE {$db_pr}users SET extensions ='" . implode(',', array_flip($_usrExt)) . "' WHERE id='{$row['id']}'");
        }
        mysqli_query($mysqli, "DELETE FROM {$db_pr}extensions WHERE id='" . $id . "' LIMIT 1");
    }
    $msgSuccess = "<div class='loginMessage loginSuccess'>Selected extensions has been successfully deleted</div>";
    echo(json_encode(array("mes" => $msg, "mesSuc" => $msgSuccess)));
}