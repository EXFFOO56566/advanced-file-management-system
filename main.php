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
$filter = "";
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
    } else {
        header("Location: index.php");
        exit();
    }
    ######################### DO NOT MODIFY (UNLESS SURE) END ########################
    $bgClass     = "even"; // default first row highlighting CSS class
    $files_table = ""; //var with php generated html table.
    $images  = array('jpg', 'png', 'gif', 'bmp');
    $music   = array('mp3', 'wma', 'ogg', 'wav');
    $video   = array('mp4', 'avi', 'flv', 'mpeg', 'wmv', 'mov');
    $docs    = array('doc', 'docx', 'xls', 'xlsx');
    $scripts = array('txt', 'bsh', 'c', 'cc', 'cpp', 'cs', 'csh', 'css', 'cyc', 'cv', 'htm', 'html', 'java', 'js', 'm', 'mxml', 'perl', 'php', 'pl', 'pm', 'py', 'rb', 'sh', 'xhtml', 'xml', 'xsl', 'sql', 'vb');
    //FILES TABLE  GENERATION TO SHOW IN HTML BELOW
    $sql = "SELECT * FROM {$db_pr}files " . $filter . " ORDER BY dateUploaded DESC LIMIT 10";
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
            $bgClass = ($bgClass == "even" ? "odd" : "even");
            $id    = $rr['id'];
            $title = $rr['title'];
            $files_table .= "<tr class=\"" . $bgClass . "\">";
            $files_table .= "";
            $files_table .= "<td class='row'>" . $rr["title"] . "</td>";
            $files_table .= "<td class='row'>" . number_format($this_file_size, 2, ".", ",") . " " . $prefix . "</td>";
            $files_table .= "<td class='row'>" . $rr["extension"] . "</td>";
            $files_table .= "<td class='row'>" . getUser($rr["userID"]) . "</td>";
            $files_table .= "<td class='row'>" . date("d M Y, H:i", strtotime($rr["dateUploaded"])) . "</td>";
            $files_table .= "<td align='right' class='row'>";

            $qTitle = '"' . $rr['title'] . '"';
            $qPath  = '"' . $rr['path'] . '"';
            if (in_array(strtolower($rr["extension"]), $images)) {
                $files_table .= "<a href=\"javascript:viewImage('{$rr['title']}','{$rr["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            if (in_array(strtolower($rr["extension"]), $music)) {
                $files_table .= "<a class=\"iframe\" href=\"javascript:viewAudio('{$rr['title']}," . urlencode($rr["path"]) . "');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            if (in_array(strtolower($rr["extension"]), $video)) {
                $files_table .= "<a class=\"iframe\" href=\"javascript:viewVideo('{$rr['title']},{$rr["path"]},{$rr["extension"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            if (strtolower($rr["extension"]) == 'pdf') {
                $files_table .= "<a class=\"iframe\" href=\"javascript:viewPdf('http://{$_SERVER['SERVER_NAME']}{$script_dir}{$rr["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            if (in_array(strtolower($rr["extension"]), $docs)) {
                $files_table .= "<a class=\"iframe\" href=\"javascript:viewDoc('http://{$_SERVER['SERVER_NAME']}{$script_dir}{$rr["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            if (in_array(strtolower($rr["extension"]), $scripts)) {
                $files_table .= "<a class=\"iframe\" href=\"javascript:viewCode('{$rr['title']},{$rr["path"]},{$rr["extension"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
            }
            $files_table .= "&nbsp;<a href=\"includes/downloadfile.php?path=" . $rr["path"] . "\"><span class='glyphicon glyphicon-cloud-download'></span></a> </td>";
            $files_table .= "</tr>";
        } // end of all files from db query (end of while loop)
        $files_table .= "<tr><td height=\"32\" colspan=\"7\">&nbsp;</td></tr>";
    } else {
        //0 files found in database. ( end of IF mysqli_num_rows > 0 )
        $files_table .= "<tr><td colspan=\"7\">0 files found in database</td></tr>";
    }
    $log_table = ""; //var with php generated html table.
    //FILES TABLE  GENERATION TO SHOW IN HTML BELOW
    $sql = "SELECT * FROM {$db_pr}activitylogs " . $filter . " ORDER BY date DESC LIMIT 10";
    $result = mysqli_query($mysqli,$sql) or die("error getting log from db");
    if (mysqli_num_rows($result) > 0) {
        while ($log_row = mysqli_fetch_assoc($result)) {
            $bgClass = ($bgClass == "even" ? "odd" : "even");
            $log_table .= "<tr class=\"" . $bgClass . "\">";
            $log_table .= "";
            $log_table .= "<td class='row'>" . getUser($log_row["userID"]) . "</td>";
            $log_table .= "<td class='row'>" . date("d M Y, H:i", strtotime($log_row["date"])) . "</td>";
            $log_table .= "<td class='row'>" . $log_row["description"] . "</td>";
            $log_table .= "</tr>";
        } // end of all files from db query (end of while loop)
        $log_table .= "<tr><td height=\"32\" colspan=\"7\">&nbsp;</td></tr>";
    } else {
        //0 files found in database. ( end of IF mysqli_num_rows > 0 )
        $log_table .= "<tr><td colspan=\"7\">0 files found in database</td></tr>";
    }
    include "includes/header.php";
    ?>
    <link href="includes/jplayer/skin/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="includes/jplayer/jquery.jplayer.min.js"></script>
    <script type="text/javascript" charset="utf-8">
        function showAlert(val) {
            var path = document.getElementById("'" + val + "'").value;
            alert(path);
        }
        function showString(str) {
            alert("id: " + str);
        }
        function viewImage(imgTitle, path) {
            $("#imgValue").html($.colorbox({href: path, initialHeight: 'auto', initialWidth: 'auto', maxWidth: '1000px', title: imgTitle}));
        }
        function viewAudio(id) {
            $("#audioValue").html($.colorbox({href: "includes/mp3.php?id=" + id, className: "inline", iframe: true, width: "480px", height: "200px"}));
        }
        function viewVideo(id) {
            $("#videoValue").html($.colorbox({href: "includes/video.php?id=" + id, className: "inline", iframe: true, width: "80%", height: "72%", maxWidth: '1100px'}));
        }
        function viewPdf(path) {
            $("#pdfValue").html($.colorbox({href: "includes/pdf/web/viewer.php?pdf=" + path, className: "inline", iframe: true, width: "100%", height: "100%", maxWidth: '1100px'}));
        }
        function viewCode(code) {
            $("#codeValue").html($.colorbox({href: "includes/google-code-prettify/view.php?file=" + code, className: "inline", iframe: true, width: "100%", height: "100%", maxWidth: '1100px'}));
        }
        function viewDoc(path) {
            $("#docValue").html($.colorbox({href: "https://docs.google.com/viewer?url=" + path + "&embedded=true", className: "inline", iframe: true, width: "100%", height: "100%", maxWidth: '1100px'}));
        }
    </script>
    <div id="content-main">
        <?php if ($level == "admin") {
            //IF ADMIN LEVEL ACCESS
            ?>
            <h2>10 Latest Files</h2>
            <table border="0" cellspacing="0" cellpadding="0" class="homeTable">
                <tr id="tblHeader">
                    <td>Title</td>
                    <td>Size</td>
                    <td>Extension</td>
                    <td>Uploader</td>
                    <td>Date Uploaded</td>
                    <td>&nbsp;</td>
                </tr>
                <?php echo $files_table; ?>
            </table>

            <h2>10 Latest Log Entries</h2>

            <table border="0" cellspacing="0" cellpadding="0" class="homeTable">
                <tr id="tblHeader">
                    <td style="width: 20%">Username</td>
                    <td style="130px">Date/Time</td>
                    <td>Action</td>
                </tr>
                <?php echo $log_table; ?>
            </table>
        <?php } else { ?>

            <h2>Welcome <?php echo $_SESSION["username"] ?></h2>
            <div class="clear"></div>
            <p>Please use menu to browse file storage or add a file.</p>
            <p>If you have proper permissions you will also be able to upload/edit files to file storage.</p>

        <?php } ?>
    </div>
    </div>
    <br>
    </div>
    <?php include "includes/footer.php";
} ?>