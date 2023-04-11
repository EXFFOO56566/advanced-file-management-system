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
@session_start();
require_once("../includes/dbconnect.php"); //Load the settings
require_once("../includes/functions.php");
$value = is_array($_GET['files']) ? $_GET['files'] : array();
$files = "<br>";
if($demo){
    echo("0 files deleted. Preview version doesn't allow file deletion, sorry.");
} else {
    foreach ($value as $id) {

        mysqli_query($mysqli, "DELETE FROM {$db_pr}messages WHERE fileID='" . $id . "' ");
        $qry = mysqli_query($mysqli, "SELECT path FROM {$db_pr}files WHERE id={$id}");
        $delete_file = mysqli_fetch_row($qry);
        $fileInfo = pathinfo($delete_file[0]);

        @unlink("../" . $delete_file[0]);
        @unlink("../{$fileInfo['dirname']}/thumbnail/{$fileInfo['basename']}");
        mysqli_query($mysqli, "DELETE FROM {$db_pr}files WHERE  id={$id}");
        $files .= "{$delete_file[0]}<br>";

    }
    $notice = sendNotice('notify_delete');
    if ($notice) {
        //send notice to system mail
        $mailData = array(
            "{%user%}" => getUser($_SESSION["idUser"]),
            "{%files%}" => $files
        );
        $subject = "File(s) were deleted!";
        sendMail(getSettings('notify_email'), $subject, "delete_file.php", $mailData);
    }

    echo(rtrim($files, "<br>"));
}
?>