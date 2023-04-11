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
session_start();
require_once("includes/dbconnect.php"); //Load the settings
$required = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT require_login_download from {$db_pr}settings WHERE id='1'"));
//check if user is logged in.
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true) {
    header("Location: index.php");
    exit();
} else {
    //get access level
    if ($required == 1) {
        if (isset($_SESSION["accesslevel"])) {
            $access = $_SESSION["accesslevel"];
            //determin admin or not.
            if (stristr($access, "abcdef")) {
                $level = "admin";
            } else {
                $level = "user";
            }
        } else {
            header("Location: index.php");
        }
    }
    //if user can edit his files or all files
    //if(stristr($access,"k") || stristr($access,"l")){
    $file     = $_REQUEST['file'];
    $idFile   = $_REQUEST['idFile'];
        if(empty($file) && !empty($idFile)){

            $sql      = "SELECT * FROM {$db_pr}files WHERE id='{$idFile}'";
            $res      = mysqli_query($mysqli,$sql);
            $fileInfo = mysqli_fetch_assoc($res);
            if (mysqli_num_rows($res) < 1) {
                die("<b>404 File not found!</b>");
            }
            $file = $fileInfo['path'];
        }
    $filename = $_SERVER['DOCUMENT_ROOT'] . $script_dir . $file;
    if (!is_file($filename)) {
        if($demo){
            die("<b>Operation not permitted in preview of the product.</b>");
        }
        die("<b>404 File not found!</b>");
    }
    //Gather relevent info about file
    $len             = filesize($filename);
    $filename1       = pathinfo($filename);
    $deliverableName = str_replace(" ", "_", $filename1['basename']);
    $file_extension  = $filename1['extension'];
    //This will set the Content-Type to the appropriate setting for the file
    switch ($file_extension) {
        case "pdf":
            $ctype = "application/pdf";
            break;
        case "exe":
            $ctype = "application/octet-stream";
            break;
        case "zip":
            $ctype = "application/zip";
            break;
        case "docx":
        case "doc":
            $ctype = "application/msword";
            break;
        case "xlsx":
        case "xls":
            $ctype = "application/vnd.ms-excel";
            break;
        case "ppt":
            $ctype = "application/vnd.ms-powerpoint";
            break;
        case "gif":
            $ctype = "image/gif";
            break;
        case "png":
            $ctype = "image/png";
            break;
        case "jpeg":
        case "jpg":
            $ctype = "image/jpg";
            break;
        case "mp3":
            $ctype = "audio/mpeg";
            break;
        case "wav":
            $ctype = "audio/x-wav";
            break;
        case "mpeg":
        case "mpg":
        case "mpe":
            $ctype = "video/mpeg";
            break;
        case "mov":
            $ctype = "video/quicktime";
            break;
        case "avi":
            $ctype = "video/x-msvideo";
            break;
        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
        case "php":
        case "htm":
        case "html":
            die("<b>Cannot be used for " . $file_extension . " files!</b>");
            break;
        // case "txt":
        default:
            $ctype = "application/force-download";
    }
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    $header = "Content-Disposition: attachment; filename=" . $deliverableName . ";";
    header($header);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $len);
    $handle = fopen($filename, "r");
    if (is_file($filename)) {
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        echo $contents;
    }
    $idUser = !empty($_SESSION['idUser']) ? $_SESSION['idUser'] : "0";
    $sql    = "INSERT INTO {$db_pr}downloads (idUser,idFile,date,size)
            VALUES ('{$idUser}','{$idFile}','" . date("Y-m-d H:i") . "','" . ($len / 1024) . "')";
    mysqli_query($mysqli,$sql);
} //end permission check.
?>