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
######################### DO NOT MODIFY (UNLESS SURE) ########################
session_start();
require_once("includes/dbconnect.php"); //Load the settings
require_once("includes/functions.php"); //Load the functions
$msg = "";
$files_table = array();
$files_table_sub = array();
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

        if(strpos($access, "t")===false && strpos($access, "i")===false && strpos($access, "g")===false){
            header("Location: index.php");
        }
    } else {
        header("Location: index.php");
    }
    ######################### DO NOT MODIFY (UNLESS SURE) END ########################
    $filter      = " WHERE 1 "; //default filter variable. getting rid of undefined variable exception.
    $bgClass     = "even"; // default first row highlighting CSS class
    $files_table = array(); //var with php generated html table.
    $view_dir    = (!empty($_REQUEST["view_dir"])) ? strip_tags(str_replace("'", "`", $_REQUEST["view_dir"])) : '';
    if ($level == "user") {
        //permission check
        $q       = "SELECT upload_dirs FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
        $res     = mysqli_query($mysqli,$q);
        $rr      = mysqli_fetch_assoc($res);
        $folders = str_replace(',', "','", $rr["upload_dirs"]);
        //get users folder
        if (!stristr($access, "i")) { //if user can't see all files - show only his files in his assigned folders
            if (stristr($access, "t")) { //if user can view only his uploaded files
                if (empty($view_dir)) {
                    $filter .= " AND userID='" . $_SESSION["idUser"] . "'";
                } else {
                    $filter .= " AND userID='" . $_SESSION["idUser"] . "'";
                    //$filter.= " AND catID='".$view_dir."' ";
                }
            }
            $pageTitle = "Files";
        } else {
            $pageTitle = "Files";
        }
    } else {
        //admin
        $pageTitle = "Files";
    }
    //paging settings
    // how many rows to show per page
    $rowsPerPage = 9999;
    // by default we show first page
    $pageNum = 1;
    // if $_GET['page'] defined, use it as page number
    if (isset($_REQUEST['page'])) {
        $pageNum = $_REQUEST['page'];
    }
    $offset = ($pageNum - 1) * $rowsPerPage;
    //CREATE PAGING LINKS
    // how many rows we have in database
    $query = "SELECT COUNT(id) AS numrows FROM {$db_pr}files " . $filter;
    $result = mysqli_query($mysqli,$query) or die('Error, query failed');
    $row     = mysqli_fetch_assoc($result);
    $numrows = $row['numrows'];
    // how many pages we have when using paging?
    $maxPage = ceil($numrows / $rowsPerPage);
    // print the link to access each page
    $self = $_SERVER['PHP_SELF'];
    $nav  = '';
    for ($page = 1; $page <= $maxPage; $page++) {
        if ($page == $pageNum) {
            $nav .= " $page "; // no need to create a link to current page
        } else {
            $nav .= " <a href=\"javascript:document.ff21.page.value='" . $page . "';document.ff21.submit();\">$page</a> ";
        }
    }
    // creating previous and next link
    // plus the link to go straight to
    // the first and last page
    if ($pageNum > 1) {
        $page  = $pageNum - 1;
        $prev  = " <a href=\"javascript:document.ff21.page.value='" . $page . "';document.ff21.submit();\">Prev</a> ";
        $first = " <a href=\"javascript:document.ff21.page.value='1';document.ff21.submit();\">1st Page</a> ";
    } else {
        $prev  = '&nbsp;'; // we're on page one, don't print previous link
        $first = '&nbsp;'; // nor the first page link
    }
    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        $next = " <a href=\"javascript:document.ff21.page.value='" . $page . "';document.ff21.submit();\">Next</a> ";
        $last = " <a href=\"javascript:document.ff21.page.value='" . $maxPage . "';document.ff21.submit();\">Last</a> ";
    } else {
        $next = '&nbsp;'; // we're on the last page, don't print next link
        $last = '&nbsp;'; // nor the last page link
    }
    //"delete selected files" action processing.

    //10-02-2011
    $c1        = 0;
    $main_attr = array();
    if ($level == "user") {
        $qN = "SELECT * FROM {$db_pr}folders WHERE parentID='0' AND id IN ('" . $folders . "')";
    } else {
        $qN = "SELECT * FROM {$db_pr}folders WHERE parentID='0'";
    }
    $resN = mysqli_query($mysqli,$qN) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($resN) > 0) {
        while ($rN = mysqli_fetch_assoc($resN)) {
            $c1++;
            $main_attr[$rN["id"]] = $rN["name"];
            //FILES TABLE  GENERATION TO SHOW IN HTML BELOW
            $sql = "SELECT * FROM {$db_pr}files " . $filter . " AND catID='" . $rN["id"] . "' ORDER BY title ASC LIMIT " . $offset . ", " . $rowsPerPage;
            //echo $sql;
            $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
            if (mysqli_num_rows($result) > 0) {
                while ($rr = mysqli_fetch_assoc($result)) {
                    if ($rr["size"] > 1048576) { //if more than 1MB show MB else show KB
                        $this_file_size = $rr["size"] / 1024 / 1024;
                        $prefix         = "MB";
                    } else {
                        $this_file_size = $rr["size"] / 1024;
                        $prefix         = "KB";
                    }
                    //PERMISSION CHECK - for showing EDIT FILE icon.
                    if ($rr["userID"] == $_SESSION["idUser"] && stristr($access, "k")) { /* logged in users file + has right to edit his files*/
                        $editable = "<a href=\"edit_file.php?id=" . $rr["id"] . "\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>";
                    } else if (stristr($access, "l")) { /* not current users file but has rigt to edit all files*/
                        $editable = "<a href=\"edit_file.php?id=" . $rr["id"] . "\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>";
                    } else { /* doesnt have right to edit files.*/
                        $editable = "";
                    }
                    $bgClass = ($bgClass == "even" ? "odd" : "even");
                    if (!isset($files_table[$rN["id"]])) {
                        $files_table[$rN["id"]] = "";
                    }
                    $files_table[$rN["id"]] .= "<tr class=\"" . $bgClass . "\">";
                    $files_table[$rN["id"]] .= "";
                    if ((stristr($access, "h") && !stristr($access, "j") && $rr["userID"] == $_SESSION["idUser"]) || (stristr($access, "j"))) {
                        //if permissions allow to delete files.
                        $files_table[$rN["id"]] .= "<td height=\"24\"><input name=\"filesToDel[]\" type=\"checkbox\" value=\"" . $rr["id"] . "\" /></td>";
                    } else {
                        $files_table[$rN["id"]] .= "<td height=\"24\">&nbsp;</td>";
                    }
                    $files_table[$rN["id"]] .= "<td>" . $rr["title"] . "</td>";
                    $files_table[$rN["id"]] .= "<td>" . number_format($this_file_size, 2, ".", ",") . " " . $prefix . "</td>";
                    $files_table[$rN["id"]] .= "<td>" . $rr["extension"] . "</td>";
                    $query2 = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
                    $result2 = mysqli_query($mysqli,$query2) or die("error getting folders from database");
                    while ($rr2 = mysqli_fetch_assoc($result2)) {
                        $new_folders_namesID[$rr2["id"]] = $rr2["name"];
                    }
                    // $files_table[$rN["id"]] .= "<td>".$new_folders_namesID[$rr["catID"]]."</td>";
                    $files_table[$rN["id"]] .= "<td>" . date("d M Y, H:i", strtotime($rr["dateUploaded"])) . "</td>";
                    $fileInfo = getFileInfo($rr["id"]);
                    //check if user can share (his / or by permission) file
                    if (strstr($access, "m") == true) {
                        $files_table[$rN["id"]] .= "<td><a href=\"sharelink.php?id=" . $rr["id"] . "\"><img src=\"images/right_16.png\" border=\"0\"></a></td>";
                    } else {
                        if ($fileInfo[3] == $_SESSION["idUser"]) {
                            $files_table[$rN["id"]] .= "<td><a href=\"sharelink.php?id=" . $rr["id"] . "\"><img src=\"images/right_16.png\" border=\"0\"></a></td>";
                        } else {
                            $files_table[$rN["id"]] .= "<td>&nbsp;</td>";
                        }
                    }
                    $files_table[$rN["id"]] .= "<td><a href=\"download.php?path=" . $rr["path"] . "\"><img src=\"images/save_16.png\" alt=\"Download File\" border=\"0\"/></a> &nbsp; " . $editable;
                    $files_table[$rN["id"]] .= "&nbsp; <a href=\"messages.php?file=" . $rr["id"] . "\"><img src=\"images/bubble_16.png\" alt=\"" . getCommentCount($rr["id"]) . " File Messages\" border=\"0\"/></a>(" . getCommentCount($rr["id"]) . ") </td></tr>";
                    $files_table[$rN["id"]] .= "</td></tr>";
                } // end of all files from db query (end of while loop)
                //show button to complete file deletion if proper permissions.
                if (stristr($access, "h") || stristr($access, "j")) {
                    $files_table[$rN["id"]] .= "<tr><td height=\"32\" colspan=\"7\"><input name=\"delete_files\" type=\"submit\" value=\"Delete Selected\"  /></td></tr>";
                } else {
                    $files_table[$rN["id"]] .= "<tr><td height=\"32\" colspan=\"7\">&nbsp;</td></tr>";
                }
            } else {
                //0 files found in database. ( end of IF mysqli_num_rows > 0 )
                if (!isset($files_table[$rN["id"]])) {
                    $files_table[$rN["id"]] = "";
                }
                $files_table[$rN["id"]] .= "<tr><td colspan=\"7\">0 files found in this folder</td></tr>";
            }
        }
    }
    // SUBS
    $c2 = 0;
    if ($level == "user") {
        $qN = "SELECT * FROM {$db_pr}folders WHERE parentID<>'0' AND id IN ('" . $folders . "')";
    } else {
        $qN = "SELECT * FROM {$db_pr}folders WHERE parentID<>'0'";
    }
    $resN = mysqli_query($mysqli,$qN) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($resN) > 0) {
        while ($rN = mysqli_fetch_assoc($resN)) {
            $c2++;
            $main_attr[$rN["id"]] = $rN["name"];
            //FILES TABLE  GENERATION TO SHOW IN HTML BELOW
            $sql = "SELECT * FROM {$db_pr}files " . $filter . " AND catID='" . $rN["id"] . "' ORDER BY title ASC LIMIT " . $offset . ", " . $rowsPerPage;
            //echo $sql;
            $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
            if (mysqli_num_rows($result) > 0) {
                while ($rr = mysqli_fetch_assoc($result)) {
                    if ($rr["size"] > 1048576) { //if more than 1MB show MB else show KB
                        $this_file_size = $rr["size"] / 1024 / 1024;
                        $prefix         = "MB";
                    } else {
                        $this_file_size = $rr["size"] / 1024;
                        $prefix         = "KB";
                    }
                    //PERMISSION CHECK - for showing EDIT FILE icon.
                    if ($rr["userID"] == $_SESSION["idUser"] && stristr($access, "k")) { /* logged in users file + has right to edit his files*/
                        $editable = "<a href=\"edit_file.php?id=" . $rr["id"] . "\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>";
                    } else if (stristr($access, "l")) { /* not current users file but has rigt to edit all files*/
                        $editable = "<a href=\"edit_file.php?id=" . $rr["id"] . "\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>";
                    } else { /* doesn't have right to edit files.*/
                        $editable = "";
                    }
                    $bgClass = ($bgClass == "even" ? "odd" : "even");
                    if (!isset($files_table_sub[$rN["parentID"]][$rN["id"]])) {
                        $files_table_sub[$rN["parentID"]][$rN["id"]] = "";
                    }
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<tr class=\"" . $bgClass . "\">";
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "";
                    if ((stristr($access, "h") && !stristr($access, "j") && $rr["userID"] == $_SESSION["idUser"]) || (stristr($access, "j"))) {
                        //if permissions allow to delete files.
                        $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td height=\"24\"><input name=\"filesToDel[]\" type=\"checkbox\" value=\"" . $rr["id"] . "\" /></td>";
                    } else {
                        $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td height=\"24\">&nbsp;</td>";
                    }
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td>" . $rr["title"] . "</td>";
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td>" . number_format($this_file_size, 2, ".", ",") . " " . $prefix . "</td>";
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td>" . $rr["extension"] . "</td>";
                    $query2 = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
                    $result2 = mysqli_query($mysqli,$query2) or die("error getting folders from database");
                    while ($rr2 = mysqli_fetch_assoc($result2)) {
                        $new_folders_namesID[$rr2["id"]] = $rr2["name"];
                    }
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td>" . date("d M Y, H:i", strtotime($rr["dateUploaded"])) . "</td>";
                    $fileInfo = getFileInfo($rr["id"]);
                    //check if user can share (his / or by permission) file
                    if (strstr($access, "m") == true) {
                        $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td><a href=\"sharelink.php?id=" . $rr["id"] . "\"><img src=\"images/right_16.png\" border=\"0\"></a></td>";
                    } else {
                        if ($fileInfo[3] == $_SESSION["idUser"]) {
                            $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td><a href=\"sharelink.php?id=" . $rr["id"] . "\"><img src=\"images/right_16.png\" border=\"0\"></a></td>";
                        } else {
                            $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td>&nbsp;</td>";
                        }
                    }
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<td><a href=\"download.php?path=" . $rr["path"] . "\"><img src=\"images/save_16.png\" alt=\"Download File\" border=\"0\"/></a> &nbsp; " . $editable;
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "&nbsp; <a href=\"messages.php?file=" . $rr["id"] . "\"><img src=\"images/bubble_16.png\" alt=\"" . getCommentCount($rr["id"]) . " File Messages\" border=\"0\"/></a>(" . getCommentCount($rr["id"]) . ") </td></tr>";
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "</td></tr>";
                } // end of all files from db query (end of while loop)
                //show button to complete file deletion if proper permissions.
                if (stristr($access, "h") || stristr($access, "j")) {
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<tr><td height=\"32\" colspan=\"7\"><input name=\"delete_files\" type=\"submit\" value=\"Delete Selected\"   /></td></tr>";
                } else {
                    $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<tr><td height=\"32\" colspan=\"7\">&nbsp;</td></tr>";
                }
            } else {
                //0 files found in database. ( end of IF mysqli_num_rows > 0 )
                if (!isset($files_table_sub[$rN["parentID"]][$rN["id"]])) {
                    $files_table_sub[$rN["parentID"]][$rN["id"]] = "";
                }
                $files_table_sub[$rN["parentID"]][$rN["id"]] .= "<tr><td colspan=\"7\">0 files found in this folder</td></tr>";
            }
        }
    }

    if (isset($_POST['sync']) && !empty($_POST['folders']) && $level=='admin') {
        $folders  = $_POST['folders'];
        $countAll = 0;
        $countFolders = 0;
        $countDelete = 0;
        $currPath = dirname(__FILE__);
        foreach ($folders as $folder) {

            $f           = explode(",", $folder);
            $fid         = $f[0];
            $_path      = $f[1];
            $folderLevel = count(explode("/",$_path));
            $path       = $currPath."/".$_path."/";
            if(!is_dir($path))
                continue;
            $list = scandir($path);
            foreach($list as $item){
                if($item=='.' ||$item=='..' )
                    continue;
                if(is_file($path.$item)){
                    $filePath = $_path."/$item";
                    $fileInfo = pathinfo($item);
                    $fileExtension = $fileInfo['extension'];
                    $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}'  AND catID='{$fid}'";
                    $res = mysqli_query($mysqli,$sql);
                    if(mysqli_num_rows($res)<1){

                        $fsize         = filesize($path.$item);
                        $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $item);
                        $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$fid}', NOW());";
                        $res =  mysqli_query($mysqli,$qry);
                        $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                        VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                        $countAll++;
                    }
                }elseif(is_dir($path.$item) && $folderLevel<3){

                    if($item=='thumbnail')
                        continue;
                    $sql = "SELECT * FROM `{$db_pr}folders` WHERE `name`='{$item}' AND parentID='$fid'";
                    $res = mysqli_query($mysqli,$sql);

                    // find files level 2
                    if(mysqli_num_rows($res)>0){
                        $subFidData = mysqli_fetch_assoc($res);
                        $subFid = $subFidData['id'];
                        if(!is_dir($path.$item))
                            continue;
                        $subList = scandir($path.$item);
                        $subPath       = $path.$item."/";
                        foreach($subList as $subItem){
                            if($subItem=='.' || $subItem=='..' )
                                continue;
                            if(is_file($subPath.$subItem)){
                                $filePath = $_path."/{$item}/$subItem";
                                $fileInfo = pathinfo($filePath);
                                $fileExtension = $fileInfo['extension'];
                                $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}' AND catID='{$subFid }'";
                                $res = mysqli_query($mysqli,$sql);
                                if(mysqli_num_rows($res)<1){

                                    $fsize         = filesize($currPath."/".$filePath);
                                    $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $subItem);
                                    $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$subFid}', NOW());";
                                    //dump($qry);
                                    $res =  mysqli_query($mysqli,$qry);
                                    $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                    VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                    $countAll++;
                                }
                            }elseif(is_dir($subPath.$subItem) && $folderLevel==1){
                                if($subItem=='thumbnail')
                                    continue;
                                $sql = "SELECT * FROM `{$db_pr}folders` WHERE `name`='{$subItem}' AND parentID='$subFid'";
                                $res = mysqli_query($mysqli,$sql);
                                if(mysqli_num_rows($res)>0){
                                    $subSubFidData = mysqli_fetch_assoc($res);
                                    $subSubFid = $subSubFidData['id'];
                                    if(!is_dir($path.$item."/".$subItem))
                                        continue;
                                    $subSubList = scandir($path.$item."/".$subItem);
                                    $subSubPath       = $path.$item."/".$subItem;
                                    //dump($subSubList);
                                    foreach($subSubList as $subSubItem){
                                        if($subSubItem=='.' ||$subSubItem=='..' )
                                            continue;
                                        if(is_file($subSubPath."/".$subSubItem)){
                                            $filePath = $_path."/{$item}/$subItem/".$subSubItem;
                                            $fileInfo = pathinfo($filePath);
                                            $fileExtension = $fileInfo['extension'];
                                            $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}' AND catID='{$subSubFid}'";
                                            $res = mysqli_query($mysqli,$sql);
                                            if(mysqli_num_rows($res)<1){

                                                $fsize         = filesize($currPath."/".$filePath);
                                                $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $subSubItem);
                                                $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$subSubFid}', NOW());";
                                                //dump($qry);
                                                $res =  mysqli_query($mysqli,$qry);
                                                $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                                VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                                $countAll++;
                                            }
                                        }
                                    }
                                }else{
                                    $subSubFolderName = $subItem;
                                    $sql = "INSERT INTO `{$db_pr}folders` SET `name`='{$subSubFolderName}', parentID='$subFid',dateCreated=NOW()";
                                    //dump($sql);
                                    $res = mysqli_query($mysqli,$sql);
                                    $subSubFid = mysqli_insert_id($mysqli);
                                    $countFolders++;
                                    $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                    VALUES (NULL, 'Added new folder " . $subSubFolderName . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");

                                    if(!is_dir($subPath.$subItem))
                                        continue;
                                    $subSubList = scandir($subPath.$subItem);
                                    $subSubPath       = $path.$item."/".$subItem;
                                    foreach($subSubList as $subSubItem){
                                        if($subSubItem=='.' ||$subSubItem=='..' )
                                            continue;
                                        if(is_file($subSubPath."/".$subSubItem)){
                                            $filePath = $_path."/{$item}/$subItem/".$subSubItem;
                                            $fileInfo = pathinfo($filePath);
                                            $fileExtension = $fileInfo['extension'];
                                            $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}' AND catID='{$subSubFid}'";
                                            $res = mysqli_query($mysqli,$sql);
                                            if(mysqli_num_rows($res)<1){

                                                $fsize         = filesize($currPath."/".$filePath);
                                                $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $subSubItem);
                                                $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$subSubFid}', NOW());";
                                                //dump($qry);
                                                $res =  mysqli_query($mysqli,$qry);
                                                $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                                    VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                                $countAll++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }else{

                        $subFolderName = $item;
                        $sql = "INSERT INTO `{$db_pr}folders` SET `name`='{$subFolderName}', parentID='$fid',dateCreated=NOW()";
                        //dump($sql);
                        $res = mysqli_query($mysqli,$sql);
                        $subFid = mysqli_insert_id($mysqli);
                        $countFolders++;

                        $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                    VALUES (NULL, 'Added new folder " . $subFolderName . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");

                        if(!is_dir($path.$item))
                            continue;
                        $subList = scandir($path.$item);
                        $subPath       = $path.$item."/";
                        //dump($subList);
                        foreach($subList as $subItem){
                            if($subItem=='.' ||$subItem=='..' )
                                continue;
                            if(is_file($subPath.$subItem)){
                                $filePath = $_path."/{$item}/$subItem";
                                $fileInfo = pathinfo($filePath);
                                $fileExtension = $fileInfo['extension'];
                                $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}' AND catID='{$subFid }'";
                                $res = mysqli_query($mysqli,$sql);
                                if(mysqli_num_rows($res)<1){

                                    $fsize         = filesize($currPath."/".$filePath);
                                    $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $subItem);
                                    $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$subFid}', NOW());";
                                    //dump($qry);
                                    $res =  mysqli_query($mysqli,$qry);
                                    $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                    VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                    $countAll++;
                                }
                            }elseif(is_dir($subPath.$subItem) && $folderLevel==1){
                                if($subItem=='thumbnail')
                                    continue;

                                $subSubFolderName = $subItem;
                                $sql = "INSERT INTO `{$db_pr}folders` SET `name`='{$subSubFolderName}', parentID='$subFid',dateCreated=NOW()";
                                //dump($sql);
                                $res = mysqli_query($mysqli,$sql);
                                $subSubFid = mysqli_insert_id($mysqli);
                                $countFolders++;
                                $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                    VALUES (NULL, 'Added new folder " . $subSubFolderName . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                if(!is_dir($subPath.$subItem))
                                    continue;
                                $subSubList = scandir($subPath.$subItem);
                                $subSubPath       = $path.$item."/".$subItem;
                                //dump($subSubList);
                                foreach($subSubList as $subSubItem){
                                    if($subSubItem=='.' ||$subSubItem=='..' )
                                        continue;
                                    if(is_file($subSubPath."/".$subSubItem)){
                                        $filePath = $_path."/{$item}/$subItem/".$subSubItem;
                                        $fileInfo = pathinfo($filePath);
                                        $fileExtension = $fileInfo['extension'];
                                        $sql = "SELECT * FROM `{$db_pr}files` WHERE path='{$filePath}' AND catID='{$subSubFid}'";
                                        $res = mysqli_query($mysqli,$sql);
                                        if(mysqli_num_rows($res)<1){

                                            $fsize         = filesize($currPath."/".$filePath);
                                            $title    = preg_replace("/\\.[^.\\s]{3,4}$/", "", $subSubItem);
                                            $qry    = "INSERT INTO `{$db_pr}files` (`title`, `path`, `size`, `extension`, `userID`, `catID`, `dateUploaded`)
                                                    VALUES ('{$title}', '{$filePath}', '$fsize', '{$fileExtension}', '" . $_SESSION["idUser"] . "', '{$subSubFid}', NOW());";
                                            //dump($qry);
                                            $res =  mysqli_query($mysqli,$qry);
                                            $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                                            VALUES (NULL, 'Added new file " . $title . "." . $fileExtension . " via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
                                            $countAll++;
                                        }
                                    }
                                }
                            }
                        }

                    }

                }
            }
            $msg .= removeItemsFromFolder($fid);

        }

        if ($countAll == 0) {
            $msg .= "<div class='loginMessage loginSuccess'>No files found to sync in the selected folder.</div>";
        } else {
            $msg .= "<div class='loginMessage loginSuccess'><strong>Success!</strong>  " . $countAll . " files discovered. You can view full list of files in activity log.</div>";
        }

         if ($countFolders == 0) {
             $msg .= "<div class='loginMessage loginSuccess'>No folders found to sync in the selected folder.</div>";
         } else {
             $msg .= "<div class='loginMessage loginSuccess'><strong>Success!</strong>  " . $countFolders . " folders discovered and added to management. You can view full list of folders in activity log.</div>";
         }
        if ($countDelete >0) {
            $msg .= "<div class='loginMessage loginWarning'>{$countDelete} files was removed from database.</div>";
        }

    }
    // ============-----==========----================------------===========--------------
    include "includes/header.php";
    ?>

    <link href="includes/jplayer/skin/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="includes/jplayer/jquery.jplayer.min.js"></script>

    <script type="text/javascript" charset="utf-8">
        $(document).ready(function () {
            $("#btnAdd").colorbox({inline: true, width: "700px", height: "513px"});
            $(".iframe").colorbox({iframe: true, width: "480px", height: "200px"});
            $(".callbacks").colorbox({
                onOpen: function () {
                    alert('onOpen: colorbox is about to open');
                },
                onLoad: function () {
                    alert('onLoad: colorbox has started to load the targeted content');
                },
                onComplete: function () {
                    alert('onComplete: colorbox has displayed the loaded content');
                },
                onCleanup: function () {
                    alert('onCleanup: colorbox has begun the close process');
                },
                onClosed: function () {
                    alert('onClosed: colorbox has completely closed');
                }
            });
            $("#jquery_jplayer_1").jPlayer({
                ready: function () {
                    $(this).jPlayer("setMedia", {
                        mp3: "folder1/when_i_was_your_man.mp3"
                    });
                },
                swfPath: "js",
                supplied: "mp3",
                wmode: "window",
                smoothPlayBar: true,
                keyEnabled: true
            });
            var oTable = $('#table').dataTable({

                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "includes/datasource/files_user.php"


            });

            $('#btndeleteselected').click(function () {
                var countSelected = $('input:checkbox[name="files[]"]:checked').length;
                if (countSelected != 0) {
                    if (confirmDelete()) {
                        var file = "";
                        var values =
                            $.ajax({
                                url: 'ajax/delete_file.php',
                                data: $("#formTable").serializeArray(),
                                success: function (data) {
                                    document.getElementById('msg').innerHTML = '<div class="loginMessage loginSuccess">Deleted the following files: ' + data + '</div>';
                                    oTable.fnDraw()
                                }
                            });
                    }
                } else {
                    alert('Nothing selected.');
                }
            });
        });
        function getStringImg(str) {
            return alert(str);
        }
        function confirmDelete() {
            return confirm("Are you sure to delete selected files?");
        }
        function viewImage(imgTitle, path) {
            $("#imgValue").html($.colorbox({href: path, initialHeight: 'auto', initialWidth: 'auto', maxWidth: '850px', title: imgTitle}));
        }
        function viewAudio(id) {
            $("#audioValue").html($.colorbox({href: "includes/mp3.php?id=" + id, className: "inline", iframe: true, width: "480px", height: "200px"}));
        }
        function viewVideo(id) {
            $("#videoValue").html($.colorbox({href: "includes/video.php?id=" + id, className: "inline", iframe: true, width: '800px', height: '600px'}));
        }
        function viewPdf(path) {
            $("#pdfValue").html($.colorbox({href: "includes/pdf/web/viewer.php?pdf=" + path, className: "inline", iframe: true, width: "850px", height: "100%"}));
        }
        function viewCode(code) {
            $("#codeValue").html($.colorbox({href: "includes/google-code-prettify/view.php?file=" + code, className: "inline", iframe: true, width: "950px", height: "100%", maxWidth: '1100px'}));
        }
        function viewDoc(path) {
            $("#docValue").html($.colorbox({href: "https://docs.google.com/viewer?url=" + path + "&embedded=true", className: "inline", iframe: true, width: "850px", height: "100%", maxWidth: '1100px'}));
        }
    </script>

    <div id="content-main">

        <h2>Files Management</h2>
        <?php if($level=='admin'){?>
        <button id="btnAdd" class='btn btn-primary rFloat' id="btnAdd" href="#inline_content">FTP Sync</button>
        <?php }?>
        <div class="clear"></div>
        <div id='msg'><?php echo $msg; ?></div>

        <br/>

        <div id="dynamic">
            <form action="#" id="formTable">
                <table cellpadding="0" cellspacing="0" border="0" class="display lra" id="table">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th width="35%">Title</th>
                        <th width="10%">Size</th>
                        <th width="8%">Extension</th>
                        <th width="10%">Folder</th>
                        <th width="20%">Date Uploaded</th>
                        <th width="18%">Link</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="6" class="dataTables_empty">Loading data from server</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Title</th>
                        <th>Size</th>
                        <th>Extension</th>
                        <th>Folder</th>
                        <th>Date Uploaded</th>
                        <th>Link</th>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <div class="spacer"></div>
        <br/><br/>
        <button id="btndeleteselected" class="btn btn-danger ">Delete Selected</button>
    </div>
    <br/><br/>

    <span id="folder"></span>

    <div id="imgValue"></div>
    <div id="audioValue"></div>
    <div id="videoValue"></div>
    <div id="pdfValue"></div>
    <div id="codeValue"></div>
    <div id="docValue"></div>
    </div>
    <?php if($level=='admin'){?>
    <div style="display:none;">
        <div id='inline_content' >
            <h2>FTP Files Synchronization</h2>

            <ol class="list">
                <li> Upload files through your FTP, make sure files are uploaded to one of the file manager folders (below list)
                </li>
                <li> When finished uploading files to FTP - select folders where you uploaded files below and click SYNC.
                </li>
                <li>It may take couple of minutes to sync files to your file manager, depending on amount of files syncing.
                </li>
            </ol>

            <form enctype="multipart/form-data" action="files.php" method="post" name="ff2"
                  class="form-horizontal popup-form">
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-5 control-label">Select folders to sync:</label>

                    <div class="col-sm-7">
                        <?php getFoldersCheck()?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-5 col-sm-5">
                        <input  type="submit" name="sync" id="create" class="btn btn-primary btn-block" value="Sync"/>
                    </div>
                </div>
            </form>


            <div class="clear"></div>

        </div>
        <div class="clear"></div>
    </div>
    <?php }?>
    <?php include "includes/footer.php";
} ?>