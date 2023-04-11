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
    }
    ######################### DO NOT MODIFY (UNLESS SURE) END ########################
    $logs_table = "";
    //show page only if admin
    if ($level == "admin") {
        //paging settings
        // how many rows to show per page
        $rowsPerPage = 14;
        // by default we show first page
        $pageNum = 1;
        // if $_GET['page'] defined, use it as page number
        if (isset($_GET['page'])) {
            $pageNum = $_GET['page'];
        }
        $offset = ($pageNum - 1) * $rowsPerPage;
        //CREATE PAGING LINKS
        // how many rows we have in database
        $query = "SELECT COUNT(id) AS numrows FROM {$db_pr}activitylogs WHERE '1' ";
        $result = mysqli_query($mysqli,$query) or die('Error, query failed');
        $row = mysqli_fetch_assoc($result);
        $numrows = $row['numrows'];
        // how many pages we have when using paging?
        $maxPage = ceil($numrows / $rowsPerPage);
        // print the link to access each page
        $self = $_SERVER['PHP_SELF'];
        $nav = '';
        for ($page = 1; $page <= $maxPage; $page++) {
            if ($page == $pageNum) {
                $nav .= " $page "; // no need to create a link to current page
            } else {
                $nav .= " <a href=\"$self?page=$page\">$page</a> ";
            }
        }
        // creating previous and next link
        // plus the link to go straight to
        // the first and last page
        if ($pageNum > 1) {
            $page = $pageNum - 1;
            $prev = " <a href=\"$self?page=$page\">Prev</a> ";
            $first = " <a href=\"$self?page=1\">1st Page</a> ";
        } else {
            $prev = '&nbsp;'; // we're on page one, don't print previous link
            $first = '&nbsp;'; // nor the first page link
        }
        if ($pageNum < $maxPage) {
            $page = $pageNum + 1;
            $next = " <a href=\"$self?page=$page\">Next</a> ";
            $last = " <a href=\"$self?page=$maxPage\">Last</a> ";
        } else {
            $next = '&nbsp;'; // we're on the last page, don't print next link
            $last = '&nbsp;'; // nor the last page link
        }
        //CREATE LOGS TABLE
        $bgClass = "even";
        $sql = "SELECT * FROM {$db_pr}activitylogs  ORDER BY date DESC LIMIT " . $offset . ", " . $rowsPerPage;
        $result = mysqli_query($mysqli,$sql) or die("error getting logs from db");
        if (mysqli_num_rows($result) > 0) {
            while ($rr = mysqli_fetch_assoc($result)) {
                $bgClass = ($bgClass == "even" ? "odd" : "even");
                $logs_table .= "<tr class=\"" . $bgClass . "\">";
                $logs_table .= "<td height=\"24\">&nbsp;" . getUser($rr["userID"]) . "</td>";
                $logs_table .= "<td>&nbsp;" . date("d M Y, H:i", strtotime($rr["date"])) . "</td>";
                $logs_table .= "<td>&nbsp;" . $rr["description"] . "</td>";
                $logs_table .= "</tr>";
            }
        } else {
            $logs_table .= "<tr><td colspan=\"7\">0 logs found in database</td></tr>";
        }
        include "includes/header.php";
        ?>
        <script type="text/javascript" charset="utf-8">
            $(document).ready(function () {
//////////
                var oTable = $('#table').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": "includes/datasource/logs.php",
                    "aaSorting": [[ 1, "desc" ]],
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        $('td:eq(0)', nRow).html(aData[0] );
                        $('td:eq(1)', nRow).html(aData[1] );
                        $('td:eq(2)', nRow).html( aData[2] );
                    }

                });
/////////////////
            });
        </script>
        <div id="content-main">
            <h2>Activity Logs</h2>
            <div id="dynamic">
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                    <thead>
                    <tr>
                        <th width="20%">Username</th>
                        <th width="30%">Date/Time</th>
                        <th width="50%">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5" class="dataTables_empty">Loading data from server</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Username</th>
                        <th>Date/Time</th>
                        <th>Action</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="spacer"></div>
            <br/><br/>
            <a class="btn btn-primary" id="btn-downloadlogs" href="includes/download_csv.php" target="_blank">Download Logs</a>
            <a class="btn btn-danger" id="btn-deletealllogs" href="delete_logs.php">Delete Logs</a>
        </div>
        <div class="clear"></div>
        <br/><br/>
        </div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>