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
require_once("../includes/functions.php"); //Load the functions
//print_r($_REQUEST);
$ids = isset($_GET['ids']) && is_array($_GET['ids']) ? $_GET['ids'] : array();
$msg = $msgSuccess = '';
$thisdir = dirname(dirname(__FILE__));

if($demo) {
    echo(json_encode(array("mes" => "<div class='loginMessage loginError'>Folder deletion not allowed in preview version</div>", "mesSuc" => '')));
} else {
    foreach ($ids as $idFolder) {
        $folderPath = "/" . getFolderPathById($idFolder);
        //remove folder from server if it is empty

        if (is_dir($thisdir . $folderPath)) {
            if (is_dir($thisdir . $folderPath . "/thumbnail")) {
                foreach (scandir($thisdir . $folderPath . "/thumbnail") as $file) {
                    @unlink($thisdir . $folderPath . "/thumbnail/" . $file);
                }
                @rmdir($thisdir . $folderPath . "/thumbnail");
            }
            if (@rmdir($thisdir . $folderPath)) {
                $msgSuccess .= "<div class='loginMessage loginSuccess'>Directory '" . $folderPath . "' was successfully deleted.</div>";
                $sql = "DELETE  FROM {$db_pr}folders WHERE id='" . $idFolder . "'";
                $result = mysqli_query($mysqli, $sql) or die("oopsy, error when tryin to delete folders");
                addLog($_SESSION["idUser"], "Deleted " . $thisdir . $folderPath . " folder.");
            } else {
                $msg .= "<div class='loginMessage loginError'>Directory '" . $folderPath . "' is not empty! Please delete files associated with this directory first.</div>";
            }
        }

    }


    echo(json_encode(array("mes" => $msg, "mesSuc" => $msgSuccess)));
}